<?php

class Dashboard extends Eloquent
{
    // -- Fields -- //
    protected $fillable = array(
        'name',
        'background',
        'number',
        'is_locked',
        'is_default',
        'active_velocity'
    );
    public $timestamps = false;

    // -- Relations -- //
    public function widgets() {return $this->hasMany('Widget');}
    public function user() { return $this->belongsTo('User'); }

    /**
     * getNextAvailablePosition
     * Return the next available position to the dashboard.
     * --------------------------------------------------
     * @param $desiredX The desired cols.
     * @param $desiredY The desired rows.
     * @param $widget The editable widget.
     * @return array of the x,y position.
     * --------------------------------------------------
    */
    public function getNextAvailablePosition($desiredX, $desiredY, $widget=null) {
        /* Iterating through the grid to find a fit. */
        for ($i = 1; $i <= SiteConstants::getGridNumberOfRows(); ++$i) {
            for ($j = 1; $j <= SiteConstants::getGridNumberOfCols(); ++$j) {
                /* Defining rectangle. */
                $rectangle = array(
                    'startX' => $j,
                    'startY' => $i,
                    'endX'   => $j + $desiredX,
                    'endY'   => $i + $desiredY
                );
                /* Respecting the grid size */
                if ( ! $this->inGrid($rectangle)) {
                    continue;
                }
                if ($this->fits($rectangle, $widget)) {
                    return '{"row":' . $i . ',"col":' . $j. ',"size_x":' . $desiredX . ', "size_y": '. $desiredY .'}';
                }
            }
        }
        /* No match, default positioning. */
        return '{"row":' . 11 . ',"col":' . 11 . ',"size_x":' . $desiredX . ', "size_y": '. $desiredY .'}';
    }

    /**
     * fits
     * Determines, whether or not, the widget fits into the position.
     * --------------------------------------------------
     * @param $rectangle Array
     * @param $skipWidget The widget to avoid conflicts with.
     * @return bool
     * --------------------------------------------------
    */
    private function fits($rectangle, $skipWidget=null) {
        /* Looking for an overlap. */
        foreach ($this->widgets as $widget) {

            if ($widget->state == 'hidden') {
                continue;
            }
            if ( ! is_null($skipWidget) && ($widget->id == $skipWidget->id)) {
                continue;
            }

            $pos = $widget->getPosition();
            $xEnd = $pos->col + $pos->size_x;
            $yEnd = $pos->row + $pos->size_y;

            $x1Overlap = ($pos->col < $rectangle['endX']);
            $x2Overlap = (($xEnd) > $rectangle['startX']);
            $y1Overlap = ($pos->row < $rectangle['endY']);
            $y2Overlap = (($yEnd) > $rectangle['startY']);

            if ($x1Overlap && $x2Overlap && $y1Overlap && $y2Overlap) {
                return false;
            }
        }

        return true;
    }

    public function save(array $options=array()) {
        /* Notify user about the change */
        $this->user->updateDashboardCache();
        return parent::save($options);
    }

    /**
     * inGrid
     * Determines, if the rectangle is in the grid.
     * --------------------------------------------------
     * @param $rectangle Array
     * @return bool
     * --------------------------------------------------
    */
    private function inGrid($rectangle) {
        if ($rectangle['endX'] > SiteConstants::getGridNumberOfCols() + 1) {
            return false;
        }
        if ($rectangle['endY'] > SiteConstants::getGridNumberOfRows() + 1) {
            return false;
        }
        return true;
    }

    /**
     * Overriding delete to update the user's cache.
    */
    public function delete() {
        /* Notify user about the change */
        $this->user->updateDashboardCache();
        $this->widgets()->delete();
        parent::delete();
    }

    /**
     * checkWidgetsIntegrity
     * Checking the overall integrity of the user's widgets.
     * --------------------------------------------------
     * @param Dashboard $dashboard
     * @return boolean
     * --------------------------------------------------
     */
    public function checkWidgetsIntegrity() {
        foreach ($this->widgets as $widget) {
            /* By default giving every widget a chance. */
            if ($widget->state != 'loading') {
                $widget->setState('active');
            }

            try {
                $widget->checkIntegrity();
            } catch (WidgetFatalException $e) {
                /* Cannot recover widget. */
                $widget->setState('setup_required');
            } catch (WidgetException $e) {
                /* A simple save might help. */
                $widget->save();
                try {
                    $widget->checkIntegrity();
                } catch (WidgetException $e) {
                    /* Did not help. */
                    Log::warning($e->getMessage());
                    $widget->setState('setup_required');
                }
            }
        }
    }

    /**
     * turnOffBrokenWidgets
     * --------------------------------------------------
     * Setting all broken widget's state.
     * @return boolean
     * --------------------------------------------------
     */
    public function turnOffBrokenWidgets() {
        foreach ($this->widgets as $widget) {
            if ($widget instanceof SharedWidget) {
                continue;
            }
            if ($widget->renderable()) {
                $templateData = $widget->getTemplateData();
            } else {
                $templateData = Widget::getDefaultTemplateData($widget);
            }
            $view = View::make($widget->getDescriptor()->getTemplateName())
                ->with('widget', $templateData);
            try {
                $view->render();
            } catch (Exception $e) {
                Log::error($e->getMessage());
                $widget->setState('rendering_error');
            }
        }
    }

    /**
     * createView
     * Creating a dashboard view.
     * --------------------------------------------------
     * @param array $params
     * @return View
     * --------------------------------------------------
     */
    public function createView() {
        $dashboard = array(
            'name'       => $this->name,
            'id'         => $this->id,
            'is_locked'  => $this->is_locked,
            'is_default' => $this->is_default,
            'velocity'   => $this->active_velocity,
            'widgets'    => array(),
        );

        /* Populating widget data. */
        $this->load('widgets');
        foreach ($this->widgets as $widget) {
            /* Loading data widgets. */
            if ( $widget instanceof DataWidget) {
                try {
                    $widget->loadData();
                } catch (ServiceNotConnected $e) {
                    $error = true;
                    $widget->setState('data_source_error');
                    $templateData = Widget::getDefaultTemplateData($widget);
                } catch (WidgetException $e) {
                    Log::warning("Data loading for widget #" . $widget->id . " failed: " . $e->getMessage());
                    $widget->setState('setup_required');
                }
            }

            $error = false;

            /* Getting template data for the widget. */
            if ($widget->renderable()) {
                try {
                    $templateData = $widget->getTemplateData();
                } catch (ServiceException $e) {
                    /* Something went wrong during data building. */
                    Log::error($e->getMessage());
                    $widget->setState('rendering_error');
                    $error = true;
                }

                if ($error) {
                    /* Falling back to default template data. */
                    $templateData = Widget::getDefaultTemplateData($widget);
                }
            } else {
                /* The widget is not renderable. */
                $templateData = Widget::getDefaultTemplateData($widget);
            }
            
            /* Adding widget to the dashboard array. */
            array_push($dashboard['widgets'], array(
                'meta'         => $widget->getTemplateMeta(),
                'templateData' => $templateData
            ));
        }

        /* Getting a list of the id, names of dashboards. */
        $dashboards = array();
        foreach ($this->user->dashboards as $iDashboard) {
            array_push($dashboards, array(
                'id'     => $iDashboard->id,
                'name'   => $iDashboard->name,
                'active' => $iDashboard->id == $dashboard['id']
            ));
        }

        return View::make('dashboard.dashboard_dummy')
            ->with('dashboard', $dashboard)
            ->with('dashboardList', $dashboards);
    }

    /**
     * changeVelocity
     * Changing the velocity of all histogram widgets.
     * --------------------------------------------------
     * @param string $velocity
     * @return boolean
     * @throws WidgetException
     * --------------------------------------------------
     */
    public function changeVelocity($velocity) {
        if ( ! in_array($velocity, SiteConstants::getVelocities())) {
            throw new WidgetException('Invalid velocity');
        }
        
        /* Changing all histogram widget's resolution. */
        foreach ($this->widgets as $widget) {
            if ($widget instanceof HistogramWidget) {
                $widget->saveSettings(array('resolution' => $velocity));
            }
        }
        
        /* Saving velocity. */ 
        $this->active_velocity = $velocity;
        $this->save();

        return true;
    }

    /**
     * applyLayout
     * Rearranging the HistorgamWidgets.
     * --------------------------------------------------
     * @param int $widgetPerRow
     * @param array $preference // not used yet.
     * --------------------------------------------------
     */
    public function applyLayout($widgetPerRow, $preference=array()) {
        if ($widgetPerRow < 2 || $widgetPerRow > 4) {
            /* Invalid arguments. */
            throw new Exception("Invalid arguments", 1);
        }

        /* Initial calculations. */
        $widgets = $this->widgets;
        $oldSizes = array();
        $notHistogramWidgets = array();

        /* Reset widget positions. */
        foreach ($widgets as $widget) {
            $pos = $widget->getPosition(); 
            $oldSizes[$widget->id] = array(
                'x' => $pos->size_x,
                'y' => $pos->size_y,
            );
            $widget->position = json_encode(array(
                'size_x' => 0,
                'size_y' => 0,
                'col'    => 0,
                'row'    => 0,
            ));
        }

        /* Reassigning the widgets. */
        foreach ($widgets as $widget) {
            /* Getting widget class name. */
            $className = get_class($widget);

            if ($widget instanceof PromoWidget) {
                /* Checking related descriptor. */
                $className = $widget->getRelatedDescriptor()->getClassName();
            }
            if (is_a($className, 'HistogramWidget', true)) {
                switch($widgetPerRow) {
                case 2: $newX = 6; $newY = 6; break;
                case 3: $newX = 4; $newY = 5; break;
                case 4: $newX = 3; $newY = 4; break;
                default:;
                }
                $widget->position = $this->getNextAvailablePosition($newX, $newY);
                $widget->save();
            } else {
                array_push($notHistogramWidgets, $widget);
            }
        }
        /* Appending the rest of the widgets. */
        foreach ($notHistogramWidgets as $widget) {
            $sizes = $oldSizes[$widget->id];
            $widget->position = $this->getNextAvailablePosition(
                $sizes['x'], $sizes['y']
            );
            $widget->save();
        }
    }

}

?>

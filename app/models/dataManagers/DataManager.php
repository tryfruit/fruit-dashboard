<?php

/* When a manager is using a webhook. */
trait WebhookDataManager {
    /**
     * getJson
     * Returning the json from the url.
     * --------------------------------------------------
     * @return array/null
     * --------------------------------------------------
     */
    private function getJson() {
        try {
            $json = file_get_contents($this->getCriteria()['url']);
        } catch (Exception $e) {
            return null;
        }
        return json_decode($json, TRUE);
    }
}

/* This class is responsible for data collection. */
class DataManager extends Eloquent
{
    /* -- Table specs -- */
    protected $table = "data_managers";

    /* -- Fields -- */
    protected $fillable = array(
        'data_id',
        'user_id',
        'descriptor_id',
        'settings_criteria',
        'update_period',
        'state',
        'last_updated'
    );

    protected $dates = array('last_updated');

    public $timestamps = FALSE;

    /* -- Relations -- */
    public function descriptor() { return $this->belongsTo('WidgetDescriptor'); }
    public function data() { return $this->belongsTo('Data', 'data_id'); }
    public function user() { return $this->belongsTo('User'); }
    public function widgets() {
        return $this->data->widgets();
    }

    /* Optimized method, not using DB query */
    public function getDescriptor() {
        return WidgetDescriptor::find($this->descriptor_id);
    }

    public function collectData($options=array())  {}
    public function initializeData() {
        $this->saveData(array());
        $this->collectData();
    }

    /**
     * getCriteriaFields
     * Returns the criteria fields.
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
    public static function getCriteriaFields() {
        $widgetClassName = str_replace(
            'DataManager', 'Widget', get_called_class()
        );
        return $widgetClassName::getCriteriaFields();
    }

    /**
     * setState
     * Setting a widget's state.
     * --------------------------------------------------
     * @param string $state
     * --------------------------------------------------
    */
    public function setState($state) {
        if ($this->state == $state) {
            return;
        }
        Log::info("Changing state of manager #" . $this->id . ' from ' . $this->state . ' to '. $state);
        $this->state = $state;
        $this->save();
        $this->setWidgetsState($state);
    }

    /**
     * getDataScheme
     * Returning default dataScheme
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
    public static function getDataScheme() {
        return array();
    }

    /**
     * setUpdatePeriod
     * Setting the instace's update period.
     * --------------------------------------------------
     * @param int $updatePeriod
     * @return array
     * --------------------------------------------------
     */
    public function setUpdatePeriod($updatePeriod) {
        $this->update_period = $updatePeriod;
        $this->save();
    }

    /**
     * createManagerFromWidget
     * Creating and returning a manager from a widget
     * --------------------------------------------------
     * @param Widget $widget
     * @return array
     * --------------------------------------------------
     */
    public static function createManagerFromWidget($widget) {
        /* Only datawidgets are relevant */
        if ( ! $widget instanceof CronWidget) {
            return null;
        }

        /* Creating manager. */
        return self::createManager(
            $widget->user(),
            $widget->getDescriptor(),
            $widget->getCriteria(),
            $widget->data
        );
    }

    /**
     * createManager
     * Creating a manager with criteria
     * --------------------------------------------------
     * @param User $user
     * @param WidgetDescriptor $descriptor
     * @param array $criteria
     * @param Data $data
     * @return array
     * --------------------------------------------------
     */
    public static function createManager($user, $descriptor, array $criteria=array(), $data=null) {
        $className = $descriptor->getDMClassName();
        $dataManager = new $className(array(
            'settings_criteria' => json_encode($criteria),
            'last_updated'      => Carbon::now(),
            'state'             => 'loading'
        ));
        $dataManager->user()->associate($user);
        $dataManager->descriptor()->associate($descriptor);

        /* Creating/assigning data. */
        if ( ! is_null($data)) {
            $dataManager->data()->associate($data);
        } else {
            $data = Data::create(array('raw_value' => '[]'));
            $dataManager->data()->associate($data);
        }

        /* Saving changes. */
        $dataManager->save();

        $dataManager->initializeData();
        $dataManager->setState('active');

        return $dataManager;
    }

    /**
     * getCriteria
     * Returning the required settings for this widget.
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
    public function getCriteria() {
        return json_decode($this->settings_criteria, 1);
    }

    /**
     * getData
     * Returning the raw data json decoded.
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
    public function getData() {
        $data = json_decode($this->data->raw_value, 1);
        if ( ! is_array($data)) {
            return array();
        }
        return $data;
    }

    /**
     * saveData
     * Saving the data to DB
     * --------------------------------------------------
     * @param data $data
     * --------------------------------------------------
     */
     public function saveData($data) {
        $this->data->raw_value = json_encode($data);
        $this->data->save();
     }

    /**
     * setWidgetsState
     * Setting the corresponding widgets state.
     * --------------------------------------------------
     * @param string $state
     * --------------------------------------------------
     */
     public function setWidgetsState($state) {
        foreach ($this->widgets as $generalWidget) {
            $generalWidget->setState($state);
        }
     }

    /**
     * checkIntegrity
     * Checking the dataManager integrity.
    */
    public function checkIntegrity() {
        $data = $this->data->raw_value;
        if ($this->state == 'loading') {
            /* Populating is underway */
            $this->setWidgetsState('loading');
        } else if (is_null(json_decode($data)) || ! $this->hasValidScheme()) {
            /* No json in data, this is a problem. */
            $this->initializeData();
            $this->setState('active');
        } else {
            $this->setState('active');
        }
    }

    /**
     * hasValidScheme
     * Checking if the scheme is valid in the data.
     * --------------------------------------------------
     * @return boolean
     * --------------------------------------------------
    */
    public function hasValidScheme() {
        $scheme = $this->getDataScheme();
        $dataScheme = json_decode($this->data->raw_value, 1);
        if ( ! is_array($dataScheme)) {
            return FALSE;
        }
        /* Iterating through the keys */
        foreach ($scheme as $key) {
            if ( ! array_key_exists($key, $dataScheme)) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Delete
     * Deleting the data as well.
     */
     public function delete() {
        $data_id = $this->data->id;
        $this->widgets()->delete();
        $result = parent::delete();
        Data::find($data_id)->delete();
        return $result;
    }

    /**
     * newFromBuilder
     * Override the base Model function to use polymorphism.
     * --------------------------------------------------
     * @param array $attributes
     * --------------------------------------------------
     */
    public function newFromBuilder($attributes=array()) {
        $className = WidgetDescriptor::find($attributes->descriptor_id)
            ->getDMClassName();
        $instance = new $className;
        $instance->exists = TRUE;
        $instance->setRawAttributes((array) $attributes, true);
        return $instance;
    }
}
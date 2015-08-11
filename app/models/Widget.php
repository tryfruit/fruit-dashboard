<?php
/* If the widget needs to be updated automatically by a cron job.  */
interface iCronWidget {
    public function collectData();
}

/* Main widget class */
class Widget extends Eloquent
{
    /* -- Table specs -- */
    protected $table = "widgets";

    /* -- Fields -- */
    protected $fillable = array(
        'state',
        'settings',
        'position'
    );
    public $timestamps = FALSE;

    /* These variables will be overwritten, with late static binding. */
    public static $settingsFields = array();
    public static $setupSettings = array();

    /* -- Relations -- */
    public function descriptor() { return $this->belongsTo('WidgetDescriptor'); }
    public function data() { return $this->belongsTo('Data', 'data_id'); }
    public function dashboard() { return $this->belongsTo('Dashboard'); }
    public function user() { return $this->dashboard->user; }

    /**
     * ================================================== *
     *                   PUBLIC SECTION                   *
     * ================================================== *
     */

    /**
     * getSettingsFields
     * --------------------------------------------------
     * Getting the settings meta.
     * @return array
     * --------------------------------------------------
    */
    public function getSettingsFields() {
        return static::$settingsFields;
    }

    /**
     * getSetupFields
     * --------------------------------------------------
     * Getting the setup settings meta.
     * @return array
     * --------------------------------------------------
    */
    public function getSetupFields() {
        return static::$setupSettings;
    }

    /**
     * getSpecific
     * --------------------------------------------------
     * Getting the correct widget from a general widget,
     * @return mixed
     * --------------------------------------------------
    */
    public function getSpecific() {
        $className = WidgetDescriptor::find($this->descriptor_id)->getClassName();
        return $className::find($this->id);
    }

    /**
     * getPosition
     * --------------------------------------------------
     * Getting the position from DB and converting it to an object.
     * @return Position Object.
     * --------------------------------------------------
    */
    public function getPosition() {
        return json_decode($this->position);
    }

    /**
     * getSettings
     * --------------------------------------------------
     * Getting the settings from db, and transforming it to assoc.
     * @return mixed
     * --------------------------------------------------
    */
    public function getSettings() {
        return json_decode($this->settings, 1);
    }

    /**
     * setPosition
     * --------------------------------------------------
     * Setting the position of the model.
     * @param array $decodedPosition
     * --------------------------------------------------
    */
    public function setPosition(array $decodedPosition) {
        $validKeys = array('size_x', 'size_y', 'col', 'row');
        $position = array();

        // Testing json position corruption.
        if ($decodedPosition === null) {
            throw new BadPosition("Invalid json postion value: $json_position", 1);
        }

        // Iterating through the positions.
        foreach($decodedPosition as $key=>$value) {
            if (in_array($key, $validKeys)) {
                // There's a match in the array, saving position.
                $position[$key] = $value;
                // Removing key to handle duplications.
                unset($validKeys[array_search($key, $validKeys)]);
            }
        }

        // The valid keys should be empty.
        if (!empty($validKeys)) {
            throw new BadPosition("Invalid json postion value: $json_position", 1);
        }

        $this->position = json_encode($position);
        $this->save();
    }

    /**
     * getSettingsValidationArray
     * --------------------------------------------------
     * Getting the laravel validation array.
     * @param array $fields
     * @return array
     * --------------------------------------------------
    */
    public function getSettingsValidationArray($fields) {
        $validationArray = array();

        foreach ($this->getSettingsFields() as $fieldName=>$fieldMeta) {
            // Not validating fields that are not present.
            if (!in_array($fieldName, $fields)) {
                continue;
            }
            $validationString = '';

            // Looking for custom validation.
            if (isset($fieldMeta['validation'])) {
                $validationString .= $fieldMeta['validation']."|";
            }

            // Doing type based validation.
            if ($fieldMeta['type'] == 'SCHOICE') {
                $validationString .= 'in:' . implode(',',array_keys($this->$fieldName()))."|";
            } else if ($fieldMeta['type'] == 'INT') {
                $validationString .= 'integer|';
            } else if ($fieldMeta['type'] == 'FLOAT') {
                $validationString .= 'numeric|';
            } else if ($fieldMeta['type'] == 'DATE') {
                $validationString .= 'date|';
            } else if ($fieldMeta['type'] == 'BOOL') {
                $validationString = '';
            }

            // Adding validation to the return array.
            if (strlen($validationString) > 0) {
                $validationArray[$fieldName] = rtrim($validationString, '|');
            }
        }

        // Return.
        return $validationArray;
    }

    /**
     * saveSettings
     * --------------------------------------------------
     * Transforming settings to JSON format. (validation done by view)
     * @param array $inputSettings
     * @param boolean $commit
     * --------------------------------------------------
    */
    public function saveSettings($inputSettings, $commit=TRUE) {
        $settings = array();
        $oldSettings = $this->getSettings();

        // Iterating through the positions.
        foreach (array_keys($this->getSettingsFields()) as $fieldName) {
            // inputSettings. oldSettings, empty string.
            if (isset($inputSettings[$fieldName])) {$settings[$fieldName] = $inputSettings[$fieldName];
            } else if (isset($oldSettings[$fieldName])) {
                // Value not set, Getting from old settings.
                $settings[$fieldName] = $oldSettings[$fieldName];
            } else if (isset($this->getSettingsFields()[$fieldName]['default'])) {
                // Value not set, default found.
                $settings[$fieldName] = $this->getSettingsFields()[$fieldName]['default'];
            } else {
                $settings[$fieldName] = "";
            }
        }

        $this->settings = json_encode($settings);

        if ($commit) {
            $this->save();
        }
    }

    /* -- Eloquent overridden methods -- */
    /**
     * Overriding save to add descriptor automatically.
     *
     * @return the saved object.
    */
    public function save(array $options=array()) {
        // Associating descriptor.
        $widgetDescriptor = WidgetDescriptor::where('type', $this->getType())->first();

        /* Checking descriptor. */
        if ($widgetDescriptor === null) {
            throw new DescriptorDoesNotExist("The descriptor for " . get_class($this) . " does not exist", 1);
        }

        // Assigning descriptor.
        $this->descriptor()->associate($widgetDescriptor);

        // Calling parent.
        parent::save($options);

        // Always saving settings to keep integrity.|
        $this->saveSettings(array(), FALSE);
        $this->checkIntegrity(FALSE);

        /* Saving integrity/settings.
         * Please note, that the save won't hit the db,
         * if no change has been made to the model.
        */
        return parent::save();

    }

    /**
     * ================================================== *
     *                  PROTECTED SECTION                 *
     * ================================================== *
    */

    /**
     * checkIntegrity
     * --------------------------------------------------
     * Checking a widget's overall integrity,
     * setting state accordingly.
     * @param boolean $commit
     * --------------------------------------------------
     */
    protected function checkIntegrity($commit=TRUE) {
        // Data integrity validation.
        if ($this instanceof DataWidget) {
            // Exception variables.
            $valid = TRUE;
            $save = FALSE;
            try {
                $this->checkData();
            } catch (MissingData $e) {
                // Data object is not present but it should be.
                $valid = FALSE;
                $save = TRUE;
                $data = Data::create(array(
                    'raw_value' => $this->createDataScheme()
                ));
                $this->data()->associate($data);
                $data->save();
            } catch (InvalidData $e) {
                // Invalid data found in db, doing cleanup. $valid = FALSE;
                $save = TRUE;
                $this->data->raw_value = $this->createDataScheme();
                $this->data->save();
            } catch (EmptyData $e) {
                // Data not yet populated.
                $valid = FALSE;
            }

            // Updating widget state accordingly.
            if (!$valid && $this->state == 'active') {
                $this->state = 'missing_data';
                $save = TRUE;
            } elseif ($valid && $this->state == 'missing_data') {
                // Valid and was missing_data -> set to active.
                $this->state = 'active';
                $save = TRUE;
            };

            if ($save && $commit) {
                $this->save();
            };

        }
    }

    /**
     * checkData
     * --------------------------------------------------
     * Checking the data integrity of the widget.
     * @throws InvalidData, MissingData, EmptyData
     * --------------------------------------------------
     */
    protected function checkData() {
        if (!$this->data) {
            throw new MissingData();
        }
        $decodedData = json_decode($this->data->raw_value, 1);
        if (!is_array($decodedData)) {
            throw new InvalidData();
        }
        if (empty($decodedData)) {
            throw new EmptyData();
        }
    }

    /**
     * getType
     * --------------------------------------------------
     * Returning the underscored type of the widget.
     * Only in generalwidget, where the descriptor is
     * still unknown.
     * (CamelCase to underscore)
     * @return string
     * --------------------------------------------------
    */
    private function getType() {
        $lowercase = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', get_class($this))), '_');
        /* Removing _widget */
        return str_replace('_widget', '', $lowercase);
    }
}

?>
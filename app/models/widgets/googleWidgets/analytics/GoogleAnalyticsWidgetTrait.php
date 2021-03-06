<?php

trait GoogleAnalyticsWidgetTrait
{
    /* -- Settings -- */
    private static $profileSettings = array(
        'profile' => array(
            'name'       => 'Profile',
            'type'       => 'SCHOICEOPTGRP',
            'validation' => 'required',
        ),
    );
    private static $profile = array('profile');

    /* Choices functions */
    public function profile() {
        $profiles = array();
        foreach ($this->user()->googleAnalyticsProperties()
           ->orderBy('name')
           ->get() as $property) {
            $profiles[$property->name] = array();
            foreach ($property->profiles as $profile) {
                $profiles[$property->name][$profile->profile_id] = $profile->name;
            }
        }
        return $profiles;
    }

    /**
     * getConnectorClass
     * --------------------------------------------------
     * Returns the connector class for the widgets.
     * @return string
     * --------------------------------------------------
     */
    public function getConnectorClass() {
        return 'GoogleAnalyticsConnector';
    }

    /**
     * getSettingsFields
     * --------------------------------------------------
     * Returns the updated settings fields
     * @return array
     * --------------------------------------------------
     */
    public static function getSettingsFields() {
        return array_merge(
            parent::getSettingsFields(), array(
                'Google Analytics settings' => self::$profileSettings,
            )
        );
    }


    /**
     * getSetupFields
     * --------------------------------------------------
     * Updating setup fields.
     * @return array
     * --------------------------------------------------
     */
    public static function getSetupFields() {
        return array_merge(parent::getSetupFields(), self::$profile);
    }

    /**
     * getCriteriaFields
     * --------------------------------------------------
     * Updating criteria fields.
     * @return array
     * --------------------------------------------------
     */
    public static function getCriteriaFields() {
        return array_merge(parent::getCriteriaFields(), self::$profile);
    }

    /**
     * getProfile
     * --------------------------------------------------
     * Return the corresponding profile.
     * @return GoogleAnalyticsProperty
     * --------------------------------------------------
    */
    public function getProfile(array $attributes=array('*')) {
        $profile = $this->user()->googleAnalyticsProfiles()
            ->where('profile_id', $this->getProfileId())
            ->first($attributes);
        /* Invalid profile in DB. */
        return $profile;
    }

    /**
     * getProperty
     * --------------------------------------------------
     * Return the corresponding property.
     * @return GoogleAnalyticsProperty
     * --------------------------------------------------
     */
    public function getProperty() {
        $profile = $this->getProfile();
        /* Invalid profile in DB. */
        if (is_null($profile)) {
            return null;
        }
        $property = $this->user()->googleAnalyticsProperties()
            ->where('property_id', $profile->property_id)
            ->first();
        return $property;
    }

    /**
     * getServiceSpecificName
     * Return the default name of the widget.
     * --------------------------------------------------
     * @return string
     * --------------------------------------------------
     */
    public function getServiceSpecificName() {
        return $this->getProperty()->name;
    }

    /**
     * getProfileId
     * --------------------------------------------------
     * Return the corresponding profile id.
     * @return GoogleAnalyticsProperty
     * --------------------------------------------------
    */
    public function getProfileId() {
        return $this->getSettings()['profile'];
    }

}

?>

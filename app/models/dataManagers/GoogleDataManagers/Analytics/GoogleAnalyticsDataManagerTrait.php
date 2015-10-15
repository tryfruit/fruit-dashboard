<?php

trait GoogleAnalyticsDataManagerTrait
{
    /**
     * getMetricNames
     * Returning the names of the metric used by the DM.
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
    public function getMetricNames() {
        return static::$metrics;
    }

    /**
     * getOptionalParams
     * Returning the optional parameters used by the DM.
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
    public function getOptionalParams() {
        return array();
    }

    /**
     * getProperty
     * --------------------------------------------------
     * Returning the corresponding property.
     * @return GoogleAnalyticsProperty
     * --------------------------------------------------
    */
    public function getProperty() {
        $profile = $this->getProfile();
        /* Invalid profile in DB. */
        if (is_null($profile)) {
            return null;
        }
        $property = $this->user->googleAnalyticsProperties()->where('property_id', $profile->property_id)->first();
        return $property;
    }

    /**
     * getProfile
     * --------------------------------------------------
     * Returning the corresponding profile.
     * @return GoogleAnalyticsProperty
     * --------------------------------------------------
    */
    public function getProfile() {
        $profile = $this->user->googleAnalyticsProfiles()
            ->where('profile_id', $this->getProfileId())
            ->first();
        /* Invalid profile in DB. */
        return $profile;
    }

    /**
     * getProfileId
     * --------------------------------------------------
     * Returning the corresponding profile id.
     * @return GoogleAnalyticsProperty
     * --------------------------------------------------
    */
    public function getProfileId() {
        return $this->getCriteria()['profile'];
    }
}
?>
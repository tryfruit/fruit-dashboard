<?php

class IframeWidget extends Widget
{
    /* -- Settings -- */
    private static $iframeSettings = array(
        'url' => array(
            'name'       => 'Iframe URL',
            'type'       => 'TEXT',
            'validation' => 'required',
            'default'    => 'http://xkcd.com',
            'help_text'  => 'The site on the url will be shown in the widget.'
        ),
        'div_id' => array(
            'name'       => 'Specific div id',
            'type'       => 'TEXT',
            'help_text'  => 'If you want to see only a specific div on the site, enter the value of the id HTML property here.'
        ),
        'pointer_events' => array(
            'name'       => 'Pointer events',
            'type'       => 'BOOL',
            'default'    => false,
            'help_text'  => 'If you set this, you won\'t be able to move the widget, but will be able to navigate in the iframe'
        ),
   );
    /* The settings to setup in the setup-wizard. */
    private static $url = array('url');

    /**
     * getSettingsFields
     * Returns the SettingsFields
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
     public static function getSettingsFields() {
        return array_merge(parent::getSettingsFields(), self::$iframeSettings);
     }

    /**
     * getSetupFields
     * Returns the SettingsFields
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
     public static function getSetupFields() {
        return array_merge(parent::getSetupFields(), self::$url);
     }

    /**
     * getCriteriaFields
     * Returns the SettingsFields
     * --------------------------------------------------
     * @return array
     * --------------------------------------------------
     */
     public static function getCriteriaFields() {
        return array_merge(parent::getCriteriaFields(), self::$url);
     }
}

?>

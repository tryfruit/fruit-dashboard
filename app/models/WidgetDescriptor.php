<?php

class WidgetDescriptor extends Eloquent
{
    // -- Fields -- //
    protected $fillable = array(
        'name',
        'description',
        'type',
        'is_premium',
        'category',
        'min_cols', 'min_rows',
        'default_cols', 'default_rows'
    );
    public $timestamps = FALSE;

    // -- Relations -- //
    public function widgets() {return $this->hasMany('Widget', 'descriptor_id');}

    /* Returning the specific widgetClass Name
     *
     * @return string The widget class Name
    */
    public function getClassName() {
        return str_replace(
            ' ', '',
            ucwords(str_replace('_',' ', $this->type))
        ) . "Widget";
    }


    /**
     * getTemplateName
     * --------------------------------------------------
     * Returning the location of the blade template.
     * @return string
     * --------------------------------------------------
    */
    public function getTemplateName() {
        return 'widget.' . $this->category . '.widget-' . $this->type;
    }
}
?>
<?php

class WidgetDescriptor extends Eloquent
{
    // -- Fields -- //
    protected $fillable = array(
        'name',
        'description',
        'type',
        'is_premium'
    );
    public $timestamps = FALSE;

    // -- Relations -- //
    public function widgets() { return $this->hasMany('Widget', 'descriptor_id'); }

}
?>
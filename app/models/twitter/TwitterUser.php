<?php

class TwitterUser extends Eloquent
{
    // -- Fields -- //
    protected $fillable = array(
        'user_id',
        'screen_name'
    );

    // -- Options -- //
    public $timestamps = false;

    // -- Relations -- //
    public function user() { return $this->belongsTo('User'); }
}

<?php

/**
* -------------------------------------------------------------------------- 
* IntercomTracker: 
*       Wrapper functions for server-side event tracking    
* Usage:
*       $tracker = new IntercomTracker();
*       $eventData = array(
*           'en' => 'Event name', // Required.
*           'meta' => array(
*               'metadata1' => 'value1',
*               'metadata2' => 'value2',
*           ),
*       );
*       $tracker->sendEvent($eventData);
* -------------------------------------------------------------------------- 
*/
class IntercomTracker {
    /* Class properties */
    private $intercom;

    /* Constructor */
    public function __construct(){
        $this->intercom = IntercomClient::factory(array(
            'app_id'  => $_ENV['INTERCOM_APP_ID'],
            'api_key' => $_ENV['INTERCOM_API_KEY'],
        ));
    }

    /**
     * sendEvent: 
     * Dispatches an event based on the arguments.
     * @param (dict) (eventData) The event data
     *     (string) (en) [Req] Event Name.
     *     (array)  (meta) Custom metadata
     * @return (boolean) (status) True if production server, else false
     */
    public function sendEvent($eventData) {
        if (App::environment('production')) {
            /* Build and send the request */
            $this->intercom->createEvent(array(
                "event_name" => $eventData['en'],
                "created_at" => Carbon::now()->timestamp,
                "user_id" => (Auth::user() ? Auth::user()->id : 0),
                "metadata" => $eventData['meta']
            ));

            /* Return */
            return true;
        } else {
            /* Return */
            return false;
        }
    }
} /* IntercomTracker */
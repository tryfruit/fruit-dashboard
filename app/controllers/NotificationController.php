<?php

/**
 * --------------------------------------------------------------------------
 * NotificationController: Handles the notifications
 * --------------------------------------------------------------------------
 */
class NotificationController extends BaseController
{
    /**
     * ================================================== *
     *                   PUBLIC SECTION                   *
     * ================================================== *
     */

     /**
     * anyTest
     * --------------------------------------------------
     * @return Renders the test page
     * --------------------------------------------------
     */
    public function anyTest() {
        /* Get the notification objects for the user */
        $notifications = Auth::user()->notifications;

        /* Render view */
        return View::make('notification.notification-test', ['notifications' => $notifications]);
    }

     /**
     * anySend
     * --------------------------------------------------
     * @param (integer) ($notificationId) The notification id
     * @return Sends the selected notification
     * --------------------------------------------------
     */
    public function anySend($notificationId) {
        /* Get the requested notification */
        $notification = Notification::where('id', $notificationId)->where('user_id', Auth::user()->id)->first();

        if ($notification == null) {
            return Redirect::route('notification.test')->with(['error' => 'We couldn\'t send the requested notification.']);
        }

        /* Send notification */
        $notification->fire();

        /* Return */
        return Redirect::route('notification.test')->with(['success' => 'Your notification has been sent.']);;
    }

    /**
     * postWidgets
     * --------------------------------------------------
     * @param (integer) ($notificationId) The notification id
     * @return Enables / disables widgets in the notification
     * --------------------------------------------------
     */
    public function postWidgets($notificationId) {
        /* Get the requested notification */
        $notification = Notification::where('id', $notificationId)->where('user_id', Auth::user()->id)->first();

        if ($notification == null) {
            return Redirect::route('notification.test')->with(['error' => 'We couldn\'t change the settings of the requested notification.']);
        }

        Log::info(Input::all());

        /* Clean and save post data */
        $selectedWidgets = array();
        foreach (Input::all() as $key => $value) {
            $number = str_replace('widget-', "", $key);
            if (is_numeric($number)) {
                array_push($selectedWidgets, $number);
            }
        }

        /* Save selected widgets */
        $notification->selected_widgets = json_encode($selectedWidgets);
        $notification->save();

        /* Render view */
        return Redirect::route('notification.test')->with(['success' => 'You successfully changed your notification settings']);;
    }

    /**
     * ================================================== *
     *                   PRIVATE SECTION                  *
     * ================================================== *
     */

} /* NotificationController */

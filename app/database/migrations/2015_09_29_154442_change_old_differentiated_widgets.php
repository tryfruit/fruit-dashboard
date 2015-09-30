<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOldDifferentiatedWidgets extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Widget::all() as $generalWidget) {
            try {
                $widget = $generalWidget->getSpecific();
                if ( ! $widget instanceof HistogramWidget) {
                    /* Not a histogram widget. */
                    continue;
                }

                if ( $widget instanceof TwitterNewFollowersWidget) {
                    $descriptor = WidgetDescriptor::where('type', 'twitter_followers')->first();
                    /* Finding data. */
                } else if ( $widget instanceof FacebookNewLikesWidget) {
                    $descriptor = WidgetDescriptor::where('type', 'facebook_likes')->first();
                }
                if (isset($descriptor)) {
                    $old_manager = $widget->data->manager;
                    $widget->descriptor()->associate($descriptor);
                    $widget->saveSettings(array('type' => 'diff'));
                    $old_manager->delete();
                    Log::info("Linked widget #" . $widget->id . " data to cumulative.");
                }

            } catch (Exception $e) {
                Log::error('Error found while running migration: ' . get_class($this) . ' on widget #' . $widget->id . '. message: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

}
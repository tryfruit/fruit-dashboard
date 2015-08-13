<?php

class TwitterNewFollowersWidget extends HistogramWidget
{
    public function getCurrentValue() {
        $collector = new TwitterDataCollector($this->user());
        /* Getting previous last data. */
        $lastData = $this->getLatestData();
        if (is_null($lastData)) {
            return $collector->getfollowerscount();
        }
        else {
            return $collector->getfollowerscount() - $lastData;
        }
    }

}
?>
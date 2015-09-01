<?php

class FacebookNewLikesWidget extends GeneralFacebookWidget
{
    public function getCurrentValue() {
        $collector = new FacebookDataCollector($this->user());
        /* Getting previous last data. */
        $lastData = $this->getLikesWidget()->getLatestData();
        if (is_null($lastData)) {
            return $collector->getTotalLikes($this->getPage());
        }
        else {
            return $collector->getTotalLikes($this->getPage()) - $lastData['value'];
        }
    }


    /**
     * getLikesWidget
     * --------------------------------------------------
     * @return One of the user's facebook likes widget.
     * --------------------------------------------------
     */
    private function getLikesWidget() {
        $descriptor = WidgetDescriptor::where('type', 'facebook_likes')->first();
        return $this->user()->widgets()->where('descriptor_id', $descriptor->id)->get()[0]->getSpecific();
    }

}
?>

<?php

class FacebookPopulateData
{
    /* -- Class properties -- */
    const DAYS = 30;

    /**
     * The facebook collector object.
     *
     * @var FacebookDataCollector
     */
    private $collector = null;

    /**
     * The user object.
     *
     * @var User
     */
    private $user = null;

    /**
     * The Facebook Page.
     *
     * @var FacebookPage
     */
    private $page = null;

    /**
     * The dataManagers.
     *
     * @var array
     */
    private $dataManagers = null;

    /**
     * Main job handler.
     */
    public function fire($job, $data) {
        $this->user = User::find($data['user_id']);
        $this->collector = new FacebookDataCollector($this->user);
        foreach ($this->user->facebookPages()->get() as $page) {
            $this->page = $page;
            $this->dataManagers = $this->getManagers();
            $this->collectData();
        }
        $job->delete();
    }

    /**
     * Populating the widgets with data.
     */
    protected function collectData() {
        /* Getting metrics. */
        $likesData       = $this->getLikes();
        $impressionsData = $this->getPageImpressions();

        /* Saving values. */
        $this->dataManagers['facebook_likes']->saveData($likesData);
        $this->dataManagers['facebook_new_likes']->saveData(HistogramDataManager::getDiff($likesData));
        $this->dataManagers['facebook_page_impressions']->saveData($impressionsData);

        foreach ($this->dataManagers as $manager) {
            $manager->setWidgetsState('active');
        }
    }

    /**
     * Getting the page specific DataManagers
     * @return array
     */
    private function getManagers() {
        $dataManagers = array();

        foreach ($this->user->dataManagers()->get() as $generalDataManager) {
            $dataManager = $generalDataManager->getSpecific();

            if (($dataManager->descriptor->category == 'facebook') && ($dataManager->getCriteria()['page'] == $this->page->id)) {

                /* Setting dataManager. */
                $dataManagers[$dataManager->descriptor->type] = $dataManager;
            }
        }

        return $dataManagers;
    }

    /**
     * Getting the last DAYS entries for a specific insight
     *
     * @param string $insight
     * @return array
     */
    private function getHistogram($insight) {
        return $this->collector->getInsight(
            $insight, $this->page,
            array(
                'since' => Carbon::now()->subDays(self::DAYS)->getTimestamp(),
                'until' => Carbon::now()->getTimestamp()
            )
        );
    }

    /**
     * Getting the data for the likes widget.
     *
     * @return array
     */
    private function getLikes() {
        $dailyLikes = $this->getHistogram('page_fans');
        $likesData = array();
        foreach ($dailyLikes[0]['values'] as $likes) {
            $date = Carbon::createFromTimestamp(strtotime($likes['end_time']))->toDateString();
            array_push($likesData, array(
                'date'      => $date,
                'value'     => $likes['value'],
                'timestamp' => time()
            ));
        }

        return $likesData;
    }

    /**
     * Getting the page_impressions for the page_impressions widget.
     *
     * @return array
     */
    private function getPageImpressions() {
        $dailyImpressions = $this->getHistogram('page_impressions_unique');
        $pageImpressionsData = array();
        foreach ($dailyImpressions[0]['values'] as $impressions) {
            $date = Carbon::createFromTimestamp(strtotime($impressions['end_time']))->toDateString();
            array_push($pageImpressionsData, array(
                'date'      => $date,
                'value'     => $impressions['value'],
                'timestamp' => time()
            ));
        }

        return $pageImpressionsData;
    }
}
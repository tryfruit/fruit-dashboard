<?php

class DataPopulator
{
    /**
     * The user object.
     *
     * @var User
     */
    protected $user = null;

    /**
     * The name of the service.
     *
     * @var string
     */
    protected $service = '';

    /**
     * The dataManagers.
     *
     * @var array
     */
    protected $dataManagers = null;

    /**
     * The dataManager criteria.
     *
     * @var array
     */
    protected $criteria = null;

    /**
     * Main job handler.
     */
    public function fire($job, $data) {
        /* Init */
        Log::info("Starting data collection at " . Carbon::now()->toDateTimeString());
        $time = microtime(TRUE);
        $this->user     = User::find($data['user_id']);
        $this->criteria = $data['criteria'];
        $this->service  = $data['service'];

        /* Getting managers. */
        $this->dataManagers = $this->getManagers();

        /* Running data collection. */
        $this->populateData();

        /* Running data collection. */
        $this->activateManagers();

        /* Finish */
        Log::info("Data collection finished and it took " . (microtime(TRUE) - $time) . " seconds to run.");

        $job->delete();
    }

    /**
     * Populating the widgets with data.
     */
    protected function populateData() {
        foreach ($this->dataManagers as $manager) {
            if ($manager->getData() == FALSE) {
                $manager->initializeData();
                $manager->setWidgetsState('active');
            }
        }
    }

    /**
     * Getting the page specific DataManagers
     * @return array
     */
    protected function getManagers() {
        $dataManagers = array();

        foreach ($this->user->dataManagers()->get() as $dataManager) {
            if ($dataManager->getDescriptor()->category == $this->service && $dataManager->getCriteria() == $this->criteria) {
                $dataManagers[$dataManager->getDescriptor()->type] = $dataManager;
            }
        }

        return $dataManagers;
    }

    /**
     * activateManagers
     * Setting all related widget's state to active.
     */
    protected function activateManagers() {
        foreach ($this->dataManagers as $manager) {
            $manager->setState('active');
        }
    }

}
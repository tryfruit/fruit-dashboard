<?php

/* All classes that have interaction with data. */
abstract class DataWidget extends Widget implements iAjaxWidget
{
    use DefaultAjaxWidgetTrait;
    /**
     * refreshWidget
     * Refreshing the widget data.
     * --------------------------------------------------
     * @return string
     * --------------------------------------------------
    */
    public function refreshWidget()
    {
        /* Setting to loading, and waiting for the collector to finish. */
        $this->setState('loading');
        $this->updateData();
        $this->setState('active');
    }

    /**
     * save
     * Looking for managers.
     * --------------------------------------------------
     * @param array $options
     * @return null
     * --------------------------------------------------
    */
    public function save(array $options=array())
    {
        parent::save($options);

        /* Assigning data. */
        if ($this->hasValidCriteria()) {
            $this->assignData();
            if ($this->state != 'setup_required') {
                $this->setState($this->data->state, FALSE);
            }
            parent::save();
        }

        return TRUE;
    }

    /**
     * assignData
     * Assigning the data to the widget.
     */
    public function assignData()
    {
        $this->data()
            ->associate($this->getDescriptor()->getDataObject($this));
    }

    /**
     * updateData
     * Refreshing the widget data.
     * --------------------------------------------------
     * @param array options
     * @return string
     * --------------------------------------------------
    */
    public function updateData(array $options=array())
    {
        try {
            $this->data->collect($options);
        } catch (ServiceException $e) {
            Log::error('An error occurred during collecting data on #' . $this->data->id );
            $this->data->setState('data_source_error');
        }
    }

    /**
     * setUpdatePeriod
     * Setting the data collection period.
     * --------------------------------------------------
     * @param int interval
     * --------------------------------------------------
    */
    public function setUpdatePeriod($interval)
    {
        $this->data->setUpdatePeriod($interval);
    }

    /**
     * getUpdatePeriod
     * Setting the data collection period.
     * --------------------------------------------------
     * @return int
     * --------------------------------------------------
    */
    public function getUpdatePeriod()
    {
        return $this->data->update_period;
    }

    /**
     * getData
     * Passing the job to the dataObject.
     */
    public function getData($postData=null)
    {
        return $this->data->decode();
    }

    /**
     * dataExists
     * Returns whether or not there is dat in the DB.
     */
    public function dataExists()
    {
        return ! is_null($this->data);
    }

    /**
     * checkIntegrity
     * adding data integrity check.
    */
    public function checkIntegrity()
    {
        parent::checkIntegrity();
        if ( ! $this->dataExists() ||
            ($this->data->decode() == FALSE && $this->data->state == 'active')) {
            throw new WidgetException;
        }
        $this->setState($this->data->state);
    }

}

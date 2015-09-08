<?php

class BraintreePopulateData
{
    /* -- Class properties -- */
    const DAYS = 30;

    /**
     * The braintree subscriptions.
     *
     * @var array
     */
    private $subscriptions = null;

    /**
     * The braintree calculator object.
     *
     * @var BraintreeCalculator.
     */
    private $calculator = null;

    /**
     * The user object.
     *
     * @var User
     */
    private $user = null;

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
        $this->calculator = new BraintreeCalculator($this->user);
        $this->subscriptions = $this->calculator->getCollector()->getAllSubscriptions();
        $this->filterSubscriptions();
        $this->dataManagers = $this->getManagers();
        $this->collectData();
        $job->delete();
    }

    /**
     * Populating the widgets with data.
     */
    protected function collectData() {
        /* Creating data for the last 30 days. */
        $metrics = $this->getMetrics();

        $this->dataManagers['braintree_mrr']->saveData($metrics['mrr']);
        $this->dataManagers['braintree_arr']->saveData($metrics['arr']);
        $this->dataManagers['braintree_arpu']->saveData($metrics['arpu']);

        foreach ($this->dataManagers as $manager) {
            $manager->setWidgetsState('active');
        }
    }

    /**
     * Getting the DataManagers
     * @return array
     */
    private function getManagers() {
        $dataManagers = array();

        foreach ($this->user->dataManagers()->get() as $generalDataManager) {
            $dataManager = $generalDataManager->getSpecific();
            if ($dataManager->descriptor->category == 'braintree') {
                /* Setting dataManager. */
                $dataManagers[$dataManager->descriptor->type] = $dataManager;
            }
        }

        return $dataManagers;
    }

    /**
     * Returning all metrics in an array.
     *
     * @return array.
    */
    private function getMetrics() {
        /* Updating subscriptions to be up to date. */
        $this->calculator->getCollector()->updateSubscriptions();

        $mrr = array();
        $arr = array();
        $arpu = array();

        for ($i = 0; $i < self::DAYS; $i++) {
            /* Calculating the date to mirror. */
            $date = Carbon::now()->subDays($i)->toDateString();
            $this->mirrorDay($date);
            array_push($mrr, array(
                'date'      => $date,
                'value'     => $this->calculator->getMrr(),
                'timestamp' => time()
            ));
            array_push($arr, array(
                'date'      => $date,
                'value'     => $this->calculator->getArr(),
                'timestamp' => time()
            ));
            array_push($arpu, array(
                'date'      => $date,
                'value'     => $this->calculator->getArpu(),
                'timestamp' => time()
            ));
        }

        /* Sorting arrays accordingly. */
        return array(
            'mrr' => $this->sortByDate($mrr),
            'arr' => $this->sortByDate($arr),
            'arpu' => $this->sortByDate($arpu),
        );
    }

    /**
     * Sorting a multidimensional dataset by date.
     *
     * @param array $dataSet
     * @return array
    */
    private function sortByDate($dataSet) {
        $dates = array();
        foreach($dataSet as $key=>$data) {
            $dates[$key] = $data['date'];
        }
        array_multisort($dates, SORT_ASC, $dataSet);
        return $dataSet;

    }

    /**
     * Filtering subscriptions to relevant only.
    */
    private function filterSubscriptions() {
        $filteredSubscriptions = array();
        foreach ($this->subscriptions as $key=>$subscription) {
            $updatedAt = Carbon::createFromTimestamp($subscription->updatedAt->getTimestamp());
            if ($updatedAt->between(Carbon::now(), Carbon::now()->subDays(30))) {
                array_push($filteredSubscriptions, $subscription);
            }
        }
        $this->subscriptions = $filteredSubscriptions;
    }

    /**
     * Trying to mirror the specific date, to our DB.
     *
     * @param date The date on which we're mirroring.
    */
    private function mirrorDay($date) {
        foreach ($this->subscriptions as $key=>$subscription) {
            foreach ($subscription->statusHistory as $statusDetail) {
                $updateDate = Carbon::createFromTimestamp($statusDetail->timestamp->getTimestamp())->toDateString();
                if ($updateDate == $date) {
                    switch ($statusDetail->status) {
                        case Braintree_Subscription::CANCELED: $this->handleSubscriptionDeletion($subscription); break;
                        case Braintree_Subscription::ACTIVE: $this->handleSubscriptionCreation($subscription); break;
                        default:;
                    }
                }
            }
        }
    }

    /**
     * Handling subscription deletion.
     *
     * @param subscription $subscription
    */
    private function handleSubscriptionDeletion($subscription) {
        $newSubscription = new BraintreeSubscription(array(
            'subscription_id' => $subscription->id,
            'start'           => $subscription->firstBillingDate,
            'status'          => Braintree_Subscription::ACTIVE,
            'customer_id'     => $subscription->transactions[0]->customer['id']
        ));

        // Creating the plan if necessary.
        $plan = BraintreePlan::where('plan_id', $subscription->planId)->first();
        if (is_null($plan)) {
            return;
        }
        $newSubscription->plan()->associate($plan);
        $newSubscription->save();
    }

    /**
     * Handling subscription creation.
     *
     * @param Subscription $subscription
    */
    private function handleSubscriptionCreation($subscription) {
        BraintreeSubscription::where('subscription_id', $subscription->id)->first()->delete();
    }
}
<?php

class StripeMrrWidget extends FinancialWidget
{
    /* -- Table specs -- */
    public static $type = 'stripe_mrr';

    public function collectData() {
        $currentData = $this->getHistogram();
        try {
            $stripeCalculator = new StripeCalculator($this->user());
            array_push($currentData, $stripeCalculator->getMrr(TRUE));
            $this->data->raw_value = json_encode($currentData);
            $this->data->save();
        } catch (StripeNotConnected $e) {
            ;
        }
    }
}
?>

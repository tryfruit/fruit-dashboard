<?php

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();
        $this->call('UserTableSeeder');
        $this->call('InitialSeeder');

        //$this->call('UserOneSeeder');
        //$this->call('UserTrialPremiumTestSeeder');
        //$this->call('UserTableExtendSeeder');
        //$this->call('supdashboarddbTableSeeder');
        //$this->call('ConnectedServicesSeeder');
        //$this->call('ExtendDefaultsSeeder');
    }

}

<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CollectData extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'widgets:collect_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running data collection.';

    /**
     * Execute the console command.
     *
     * @return none
     */
    public function fire()
    {
        /* Iterating through the managers. */
        Log::info("Data collection started at " . Carbon::now()->toDateTimeString());
        $time = microtime(TRUE);
        $errors = 0;
        $i = 0;
        foreach (Data::all() as $data) {
            if (Carbon::now()->diffInMinutes($data->updated_at) >= $data->update_period) {
                $i++;
                try {
                    $data->collect();
                } catch (ServiceException $e) {
                    $errors++;
                    Log::error('Data source error occurred on data #' . $data->id . '. message: ' . $e->getMessage());
                    $data->setState('data_source_error');
                } catch (Exception $e) {
                    $errors++;
                    Log::error('Error found while collecting data on data #' . $data->id . '. message: ' . $e->getMessage());

                }
            }
        }
        Log::info('Data collection finished with ' . $errors . ' errors (of ' . $i . ') It took ' . ( microtime(TRUE) - $time) . ' seconds to run.');
    }
}

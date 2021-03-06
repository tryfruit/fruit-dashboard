<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CleanupData extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'data:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleaning up hourly data on histogram data managers that is over 2 weeks old.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        Log::info("Data cleanup started at " . Carbon::now()->toDateTimeString());
        $time = microtime(true);
        $allDeletionCount = 0;
        foreach (Data::all() as $data) {
            try {
                if ($data->getManager() instanceof HistogramDataManager) {
                    $allDeletionCount += $data->cleanupData();
                }
            } catch (Exception $e) {
                Log::error("An error occurred while cleaning up data #" . $data->id . "." . $e->getMessage());

            }
        }
        Log::info('Data cleanup finished with ' . $allDeletionCount . ' deletions. It took ' . ( microtime(true) - $time) . ' seconds to run.');
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FetchDpdDeliveryLocations;
use Illuminate\Console\Command;

class FetchDpdDeliveryLocationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dpd:fetch-delivery-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch DPD HR delivery locations from SFTP (runs job synchronously)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fetching DPD delivery locations...');

        try {
            FetchDpdDeliveryLocations::dispatchSync();
            $this->info('Done.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->line($e->getTraceAsString());
            }
            return self::FAILURE;
        }
    }
}

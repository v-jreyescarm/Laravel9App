<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Actions\ProcessK2GetSubmissionFromDatabase;
use App\Actions\ProcessHubspotSubmissionFromDatabase;

class OneMinuteCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs Once a minute';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ProcessK2GetSubmissionFromDatabase::handle();
        ProcessHubspotSubmissionFromDatabase::handle();

        return 0;
    }
}

<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LaravelScheduledTasks1MinCron extends Controller
{
    /**
     * Handle 1 minute cron
     */

    protected function runSchedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('cron:minute')
            ->everyMinute();

        // \Log::info("LaravelScheduledTasks1MinCron > runSchedule  is working as expected!");

        // return true;
    }
}

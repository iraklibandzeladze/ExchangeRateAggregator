<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\FetchRates;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        FetchRates::class,
    ];


    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('rates:fetch')
            ->timezone('Asia/Tbilisi')
            ->dailyAt('08:00');
    }
}

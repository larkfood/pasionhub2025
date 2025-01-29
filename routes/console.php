<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes (for Closure Commands)
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
| Note:
| Check out the class based commands in the 'app/Console/Commands/' directory
|
| Important:
| The class based commands should not be declared here.
| If a closure command and a class based command have the same signature,
| this is the closure command that will be executed.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Console Schedule
|--------------------------------------------------------------------------
|
| Below you may define your scheduled tasks, including console commands
| or system commands. These tasks will be run automatically when due
| using Laravel's built-in "schedule:run" Artisan console command.
|
| Note:
| This part can be overridden with the '->withSchedule()' method in the
| "bootstrap/app.php" file. So, check this file before any change here.
|
*/

// Schedule::command('inspire')->hourly();

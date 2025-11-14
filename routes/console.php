<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('infor:production-plan')->cron('0,30 * * * *');

Schedule::command('infor:item-class')->cron('0 0 * * *');
Schedule::command('infor:work-center')->cron('0 0 * * *');
Schedule::command('infor:standard-pack')->cron('0 0 * * *');
Schedule::command('infor:project')->cron('0 0 * * *');

Schedule::command('infor:part-number')->cron('0 1 */3 * *');
Schedule::command('infor:next-process')->cron('0 2 */3 * *');

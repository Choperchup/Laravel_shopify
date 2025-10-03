<?php

protected function schedule(Schedule $schedule): void {
    $schedule->command('rules:process')->everyMinute();
}
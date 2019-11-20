<?php

namespace App\Providers;

use Illuminate\Log\LogServiceProvider as BaseLogServiceProvider;
use Illuminate\Log\Writer;

class LogServiceProvider extends BaseLogServiceProvider
{
    /**
     * Configure the Monolog handlers for the application.
     *
     * @param  \Illuminate\Log\Writer  $log
     * @return void
     */
    protected function configureDailyHandler(Writer $log)
    {
        $path = config('app.log_path', '/tmp/laravel.log');
        $log->useDailyFiles(
            $path, $this->maxFiles(),
            $this->logLevel()
        );
    }
}

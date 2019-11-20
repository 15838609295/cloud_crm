<?php

/**
 *
 * 自定义Application 实现自定义LOG目录
 * laravel log配置在 .env  env('APP_LOG_PATH')
 */
namespace App;

use Illuminate\Foundation\Application AS BaseApplication;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Routing\RoutingServiceProvider;
use App\Providers\LogServiceProvider;

class Application extends BaseApplication
{

    /**
     * Create a new laravel application instance.
     *
     * @param  string|null  $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        if (! empty(env('APP_TIMEZONE'))) {
            date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
        }

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerBaseBindings();

        $this->registerBaseServiceProviders();

        $this->registerCoreContainerAliases();
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));

        $this->register(new LogServiceProvider($this));

        $this->register(new RoutingServiceProvider($this));
    }

    public function bootstrapPath($path = '')
    {
        return '/tmp'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

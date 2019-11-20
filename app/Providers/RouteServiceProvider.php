<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider

{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //
        parent::boot();

    }
    /**
     * Define the routes for the application.
     *
     * @return voi
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapAdminRoutes();
        $this->mapTencentRoutes();
        $this->mapWxapiRoutes();
        //

    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapAdminRoutes()
    {
        Route::group([
            'prefix'=>'/admin',
            'middleware' => 'admin',
            'namespace' => 'App\Http\Controllers\Admin',
        ], function ($router) {
            require base_path('routes/admin.php');
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }
    /*
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    protected function mapTencentRoutes()
    {
        Route::group([
            'middleware' => 'tencent',
            'namespace' => $this->namespace,
            'prefix' => 'tencent',
        ], function ($router) {
            require base_path('routes/tencent.php');
        });
    }

    protected function mapWxapiRoutes()
    {
        Route::group([
            'middleware' => 'wxapi',
            'namespace' => $this->namespace,
            'prefix' => 'wxapi',
        ], function ($router) {
            require base_path('routes/wxapi.php');
        });
    }
}


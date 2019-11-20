<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //全局打印sql语句到log文件
//        DB::listen(function ($query) {
//            $tmp = str_replace('?', '"'.'%s'.'"', $query->sql);
//            $qBindings = [];
//            foreach ($query->bindings as $key => $value) {
//                if (is_numeric($key)) {
//                    $qBindings[] = $value;
//                } else {
//                    $tmp = str_replace(':'.$key, '"'.$value.'"', $tmp);
//                }
//            }
//            $tmp = vsprintf($tmp, $qBindings);
//            $tmp = str_replace("\\", "", $tmp);
//            Log::info(' execution time: '.$query->time.'ms; '.$tmp."\n\n\t");
//
//        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

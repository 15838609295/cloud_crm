<?php

namespace App\Providers;

use App\Http\Requests\Request;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use League\Flysystem\Exception;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];


    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(GateContract $gate)
    {
        global $scf_data;
        if (!empty($_SERVER['SCRIPT_NAME']) && strtolower($_SERVER['SCRIPT_NAME']) === 'artisan') {
            return false;
        }
        $gate->before(function ($user, $ability) {
            if ($user->id === 1) {
                return true;
            }
        });
        $this->registerPolicies($gate);

        if ($scf_data["IS_SCF"] != true){
            $url = $_SERVER['REQUEST_URI'];
            if (!in_array($url,['/public/admin/install/index',
                '/public/admin/install/testing',
                '/public/admin/install/checkDir',
                '/public/admin/install/mkDatabase',
                '/public/admin/install/formatDataBase',
                '/public/admin/test',
            ])){
                $permissions = \App\Models\Admin\Permission::with('roles')->get();
                foreach ($permissions as $permission) {
                    $gate->define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermission($permission);
                    });
                }
            }
        }else{
            $permissions = \App\Models\Admin\Permission::with('roles')->get();
            foreach ($permissions as $permission) {
                $gate->define($permission->name, function ($user) use ($permission) {
                    return $user->hasPermission($permission);
                });
            }
        }
    }


}

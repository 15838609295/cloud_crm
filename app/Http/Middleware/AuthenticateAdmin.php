<?php

namespace App\Http\Middleware;

use Closure;
use Route, URL, Auth;

class AuthenticateAdmin
{

    protected $except = [
        'admin/index',
    ];

    /**
     * Handle an incoming request.
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('admin')->user()->id === 1) {
            return $next($request);
        }else{
        	if (Auth::guard('admin')->user()->state === 0 && Auth::guard('admin')->check()) {
                Auth::guard('admin')->logout();
                return redirect()->guest("/admin/login")->withErrors('账号被冻结，请联系管理员！');
            }
        }

        $previousUrl = URL::previous();
        $routeName = starts_with(Route::currentRouteName(), 'admin.') ? Route::currentRouteName() : 'admin.' . Route::currentRouteName();
        //路由访问转小写
        $routeName = strtolower($routeName);
        if (!\Gate::forUser(auth('admin')->user())->check($routeName)) {
            if ($request->ajax() && ($request->getMethod() != 'GET')) {
                return response()->json([
                    'status' => -1,
                    'code'   => 403,
                    'msg'    => '您没有权限执行此操作',
                ]);
            } else {
                return response()->view('admin.errors.403', compact('previousUrl'));
            }
        }

        return $next($request);
    }
}

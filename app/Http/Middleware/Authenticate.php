<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard('web')->user()){
            if (Auth::guard('web')->user()->status === 1 && Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
                return redirect()->guest("/login")->withErrors('账号被冻结，请联系管理员！');
            }
        }
        
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                $login_path = [
                    'admin' => '/admin/login',
                ];
                $url = empty($guard) ? '/login' : (isset($login_path[$guard]) ? $login_path[$guard] : '/login');

                return redirect()->guest($url);
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Support\AuthRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if (AuthRedirect::isAdminRequest($request)) {
                    if ($user && $user->isAdmin()) {
                        return redirect()->to(AuthRedirect::pullAdminDestination($request));
                    }

                    $request->session()->forget([
                        AuthRedirect::ADMIN_INTENDED,
                        'url.intended',
                    ]);

                    return redirect()->to(AuthRedirect::storefrontHome($request));
                }

                return redirect()->to(AuthRedirect::pullStorefrontDestination($request));
            }
        }

        return $next($request);
    }
}

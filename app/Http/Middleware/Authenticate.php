<?php

namespace App\Http\Middleware;

use App\Support\AuthRedirect;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if (AuthRedirect::isAdminRequest($request)) {
                AuthRedirect::rememberAdminIntended($request);

                return AuthRedirect::adminLogin($request);
            }

            return route('login');
        }
    }
}

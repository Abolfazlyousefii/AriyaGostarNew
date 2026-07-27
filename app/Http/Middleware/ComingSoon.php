<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComingSoon
{
    /**
     * Coming-soon mode is fully disabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
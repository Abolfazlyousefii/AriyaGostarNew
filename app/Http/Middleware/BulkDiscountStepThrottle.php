<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/** Separate small admin apply budget from the preview budget, both per-user. */
final class BulkDiscountStepThrottle
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return response('Unauthorized', 401);
        }

        $step = $request->input('step') === 'apply' ? 'apply' : 'preview';
        $maximum = $step === 'apply' ? 3 : 30;
        $key = 'ariya:bulk-discounts:' . $step . ':user:' . $user->getAuthIdentifier();

        if (RateLimiter::tooManyAttempts($key, $maximum)) {
            $retryAfter = max(1, (int) RateLimiter::availableIn($key));
            return response('Too Many Requests', 429)
                ->header('Retry-After', (string) $retryAfter)
                ->header('Cache-Control', 'private, no-store');
        }

        RateLimiter::hit($key, 60);
        return $next($request);
    }
}

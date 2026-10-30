<?php

namespace App\Http\Middleware;

use App\Support\CustomerJourneySafe;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class RecordCustomerJourney
{
    public function handle($request, Closure $next)
    {
        if (!config('customer_journey.enabled') || ($request->is('admin*') || $request->is('*/admin/*')) || ($request->is('customer-journey/*') || $request->is('*/customer-journey/*'))) {
            return $next($request);
        }

        $started = microtime(true);
        // Never let telemetry's user resolution become a new failure mode for checkout.
        $userId = 0;
        try {
            $userId = (int) (optional($request->user())->id ?: 0);
        } catch (Throwable $ignored) {
            // Continue the original request unchanged.
        }
        $requestId = bin2hex(random_bytes(8));
        $status = 500;
        $exceptionType = null;
        $exceptionFile = null;
        $exceptionLine = null;
        try {
            $response = $next($request);
            $status = (int) $response->getStatusCode();
            return $response;
        } catch (Throwable $e) {
            $exceptionType = class_basename($e);
            // Only a sanitized filename and line number; never raw stack traces or messages.
            $exceptionFile = CustomerJourneySafe::sourceFile($e->getFile());
            $exceptionLine = max(1, min(4294967295, (int) $e->getLine()));
            if ($e instanceof ValidationException) $status = 422;
            elseif ($e instanceof AuthenticationException) $status = 401;
            elseif ($e instanceof AuthorizationException) $status = 403;
            elseif ($e instanceof HttpExceptionInterface) $status = $e->getStatusCode();
            throw $e;
        } finally {
            // Every database failure must leave the storefront response unchanged.
            try {
                $userId = $userId ?: (int) (optional($request->user())->id ?: 0);
                if ($userId > 0) {
                    DB::table('customer_journey_events')->insert([
                        'user_id' => $userId,
                        'kind' => 'request',
                        'event' => $status >= 400 ? 'http_error' : 'http_request',
                        'request_id' => $requestId,
                        'method' => substr($request->method(), 0, 8),
                        'route_name' => CustomerJourneySafe::route(optional($request->route())->getName()),
                        'path' => CustomerJourneySafe::path($request->path()),
                        'http_status' => $status,
                        'duration_ms' => min(4294967295, max(0, (int) round((microtime(true) - $started) * 1000))),
                        'error_class' => CustomerJourneySafe::errorClass($exceptionType),
                        'source_file' => $exceptionFile,
                        'source_line' => $exceptionLine,
                        'created_at' => now(),
                    ]);
                }
            } catch (Throwable $loggingFailure) {
                // Never leak details to customers or break cart/checkout.
                // Rate limiting log warnings avoids recursive write amplification.
            }
        }
    }
}

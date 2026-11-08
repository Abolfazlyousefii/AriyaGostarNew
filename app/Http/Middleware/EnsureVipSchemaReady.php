<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

final class EnsureVipSchemaReady
{
    public function handle(Request $request, Closure $next)
    {
        // No writes here; the local VIP migrations must be reviewed and applied separately.
        foreach (['vip_discount_settings', 'vip_customer_memberships', 'vip_customer_badges'] as $table) {
            if (!Schema::hasTable($table)) {
                abort(503, 'VIP schema is not ready on the development database.');
            }
        }

        return $next($request);
    }
}

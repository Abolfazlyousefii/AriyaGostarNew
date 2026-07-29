<?php

namespace App\Http\Controllers;

use App\Models\LiveVisitor;
use App\Models\LiveVisitorEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Throwable;

class LiveVisitorTrackerController extends Controller
{
    public function collect(Request $request): JsonResponse
    {
        $data = $request->validate([
            'visitor_token' => ['nullable', 'string', 'max:64'],
            'event_type' => ['required', 'in:page_view,heartbeat,product_view,search,cart_update,checkout_start'],
            'url' => ['nullable', 'string', 'max:2048'],
            'path' => ['nullable', 'string', 'max:1024'],
            'title' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'product_id' => ['nullable', 'integer', 'min:1'],
            'product_title' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'category_title' => ['nullable', 'string', 'max:255'],
            'search_term' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:120'],
            'device_label' => ['nullable', 'string', 'max:120'],
            'screen' => ['nullable', 'string', 'max:40'],
            'viewport' => ['nullable', 'string', 'max:40'],
            'language' => ['nullable', 'string', 'max:20'],
            'connection' => ['nullable', 'string', 'max:40'],
        ]);

        $agent = new Agent();
        $agent->setUserAgent((string) $request->userAgent());

        if ($agent->isRobot()) {
            return response()->json(['status' => 'ignored']);
        }

        $token = $data['visitor_token'] ?? (string) Str::uuid();
        $user = $request->user();
        $ip = $request->ip();
        $isLocal = in_array($ip, ['127.0.0.1', '::1'], true);
        [$cartItems, $cartTotal] = $this->cartSnapshot();

        $visitor = LiveVisitor::firstOrCreate(
            ['visitor_token' => $token],
            ['started_at' => now(), 'last_seen_at' => now()]
        );
        $eventType = $data['event_type'];
        $scoreIncrement = $this->scoreIncrement($eventType);
        $baseScore = (int) ($visitor->intent_score ?? 0);
        $cartScore = min(40, $cartItems * 8);
        $intentScore = min(100, max($baseScore + $scoreIncrement, $cartScore));

        $visitor->fill([
            'user_id' => $user?->id,
            'session_id' => $request->session()->getId(),
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'device_type' => $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop'),
            'device_name' => $data['device_label'] ?? $agent->device(),
            'platform' => $agent->platform(),
            'browser' => $agent->browser(),
            'country' => $isLocal ? 'لوکال' : ($request->header('CF-IPCountry') ?: $request->header('X-Country-Name')),
            'region' => $request->header('X-Region-Name'),
            'city' => $isLocal ? 'سیستم محلی' : $request->header('X-City-Name'),
            'timezone' => $data['timezone'] ?? null,
            'current_url' => $data['url'] ?? $request->fullUrl(),
            'current_path' => $data['path'] ?? $request->path(),
            'current_title' => $data['title'] ?? null,
            'referrer' => $data['referrer'] ?? null,
            'current_product_id' => $data['product_id'] ?? $visitor->current_product_id,
            'current_product_title' => $data['product_title'] ?? $visitor->current_product_title,
            'current_category_id' => $data['category_id'] ?? $visitor->current_category_id,
            'current_category_title' => $data['category_title'] ?? $visitor->current_category_title,
            'search_term' => $data['search_term'] ?? null,
            'cart_items_count' => $cartItems,
            'cart_total' => $cartTotal,
            'intent_score' => $intentScore,
            'metadata' => [
                'screen' => $data['screen'] ?? null,
                'viewport' => $data['viewport'] ?? null,
                'language' => $data['language'] ?? null,
                'connection' => $data['connection'] ?? null,
            ],
            'last_seen_at' => now(),
        ]);

        if (! $visitor->started_at) {
            $visitor->started_at = now();
        }

        $visitor->save();

        if ($eventType !== 'heartbeat') {
            LiveVisitorEvent::create([
                'live_visitor_id' => $visitor->id,
                'user_id' => $user?->id,
                'event_type' => $eventType,
                'url' => $data['url'] ?? null,
                'path' => $data['path'] ?? null,
                'title' => $data['title'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'product_title' => $data['product_title'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'category_title' => $data['category_title'] ?? null,
                'search_term' => $data['search_term'] ?? null,
                'metadata' => [
                    'referrer' => $data['referrer'] ?? null,
                    'cart_items_count' => $cartItems,
                    'cart_total' => $cartTotal,
                ],
                'occurred_at' => now(),
            ]);
        }

        $this->occasionallyCleanup();

        return response()->json([
            'status' => 'ok',
            'visitor_token' => $token,
            'last_seen_at' => $visitor->last_seen_at?->toIso8601String(),
        ])->header('Cache-Control', 'no-store');
    }

    private function cartSnapshot(): array
    {
        try {
            $cart = get_cart();

            if (! $cart) {
                return [0, 0];
            }

            $items = (int) DB::table('cart_product')
                ->where('cart_id', $cart->id)
                ->sum('quantity');

            $total = (float) DB::table('cart_product as cp')
                ->leftJoin('prices as p', 'p.id', '=', 'cp.price_id')
                ->where('cp.cart_id', $cart->id)
                ->sum(DB::raw('cp.quantity * COALESCE(p.price, 0)'));

            return [$items, $total];
        } catch (Throwable) {
            return [0, 0];
        }
    }

    private function scoreIncrement(string $eventType): int
    {
        return match ($eventType) {
            'product_view' => 8,
            'search' => 5,
            'cart_update' => 20,
            'checkout_start' => 35,
            'page_view' => 1,
            default => 0,
        };
    }

    private function occasionallyCleanup(): void
    {
        try {
            if (random_int(1, 200) !== 1) {
                return;
            }

            LiveVisitorEvent::where('occurred_at', '<', now()->subDays(60))->delete();
            LiveVisitor::where('last_seen_at', '<', now()->subDays(30))->delete();
        } catch (Throwable) {
            // Tracking must never interrupt the storefront.
        }
    }
}

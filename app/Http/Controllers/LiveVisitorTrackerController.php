<?php

namespace App\Http\Controllers;

use App\Models\LiveVisitor;
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
        /*
         * IMPORTANT:
         * Analytics must never interrupt the storefront.
         * The current live_visitors schema uses visitor_uuid, not visitor_token.
         * We keep visitor_token in the request/response only for compatibility
         * with the existing browser JavaScript.
         */
        try {
            $data = $request->validate([
                'visitor_token' => ['nullable', 'string', 'max:64'],
                'event_type' => ['required', 'in:page_view,heartbeat,product_view,search,cart_update,checkout_start'],
                'url' => ['nullable', 'string', 'max:2048'],
                'path' => ['nullable', 'string', 'max:1024'],
                'title' => ['nullable', 'string', 'max:255'],
                'referrer' => ['nullable', 'string', 'max:2048'],
                'product_id' => ['nullable', 'integer', 'min:1'],
                'product_title' => ['nullable', 'string', 'max:255'],
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

            $uuid = trim((string) ($data['visitor_token'] ?? ''));

            if ($uuid === '') {
                $uuid = (string) Str::uuid();
            }

            $user = $request->user();
            [$cartItems, $cartTotal] = $this->cartSnapshot();
            $now = now();

            $visitor = LiveVisitor::firstOrNew([
                'visitor_uuid' => $uuid,
            ]);

            $isNew = ! $visitor->exists;

            if ($isNew) {
                $visitor->first_seen_at = $now;
                $visitor->page_views = 0;
            }

            if (($data['event_type'] ?? null) === 'page_view') {
                $visitor->page_views = ((int) $visitor->page_views) + 1;
            }

            $visitor->fill([
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'user_id' => $user?->id,
                'phone' => $user?->username,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 2000, ''),
                'device_type' => $agent->isTablet() ? 'tablet' : ($agent->isMobile() ? 'mobile' : 'desktop'),
                'device_name' => $data['device_label'] ?? $agent->device(),
                'platform' => $agent->platform(),
                'browser' => $agent->browser(),
                'current_url' => $data['url'] ?? $request->url(),
                'current_path' => $this->normalizedPath($data['path'] ?? $request->path()),
                'current_page_title' => $data['title'] ?? null,
                'current_action' => $this->actionLabel((string) $data['event_type'], $data['product_title'] ?? null),
                'current_product_id' => $data['product_id'] ?? null,
                'current_product_title' => $data['product_title'] ?? null,
                'referrer' => $data['referrer'] ?? null,
                'cart_items_count' => $cartItems,
                'cart_total' => $cartTotal,
                'metadata' => [
                    'timezone' => $data['timezone'] ?? null,
                    'screen' => $data['screen'] ?? null,
                    'viewport' => $data['viewport'] ?? null,
                    'language' => $data['language'] ?? null,
                    'connection' => $data['connection'] ?? null,
                ],
                'last_seen_at' => $now,
                'last_activity_at' => $now,
            ]);

            $visitor->save();

            return response()->json([
                'status' => 'ok',
                'visitor_token' => $uuid,
                'last_seen_at' => $now->toIso8601String(),
            ])->header('Cache-Control', 'no-store');
        } catch (Throwable $exception) {
            report($exception);

            // Tracking failure must never produce a storefront 500.
            return response()->json([
                'status' => 'ignored',
            ])->header('Cache-Control', 'no-store');
        }
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

    private function normalizedPath(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return '/';
        }

        return '/' . ltrim($path, '/');
    }

    private function actionLabel(string $eventType, ?string $productTitle): string
    {
        return match ($eventType) {
            'product_view' => $productTitle ? 'مشاهده محصول: ' . Str::limit($productTitle, 180) : 'مشاهده محصول',
            'search' => 'جستجو',
            'cart_update' => 'تغییر سبد خرید',
            'checkout_start' => 'شروع ثبت سفارش',
            'heartbeat' => 'فعال در سایت',
            default => 'مشاهده صفحه',
        };
    }
}
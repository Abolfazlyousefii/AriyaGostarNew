<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\LiveVisitor;
use App\Support\LiveVisitorSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class LiveVisitorController extends Controller
{
    private const ONLINE_MINUTES = 5;

    public function index(Request $request): View|JsonResponse
    {
        try {
            LiveVisitorSchema::ensure();
            $payload = $this->payload();
        } catch (Throwable $exception) {
            report($exception);

            if ($request->expectsJson() || $request->ajax() || $request->boolean('json')) {
                return response()->json([
                    'ok' => false,
                    'message' => 'ساخت جدول‌های بازدید زنده انجام نشد. دسترسی CREATE/ALTER دیتابیس را بررسی کنید.',
                ], 500);
            }

            $payload = $this->emptyPayload($exception->getMessage());
        }

        if ($request->expectsJson() || $request->ajax() || $request->boolean('json')) {
            return response()->json($payload);
        }

        return view('back.statistics.live-visitors.index', [
            'title' => 'بازدیدکنندگان زنده',
            'initialPayload' => $payload,
        ]);
    }

    public function data(): JsonResponse
    {
        LiveVisitorSchema::ensure();
        return response()->json($this->payload());
    }

    public function purge(Request $request): JsonResponse
    {
        LiveVisitorSchema::ensure();

        $days = max(1, min(365, (int) $request->input('days', 30)));
        $deleted = LiveVisitor::where('last_activity_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'ok' => true,
            'deleted' => $deleted,
        ]);
    }

    private function payload(): array
    {
        if (!Schema::hasTable('live_visitors')) {
            return $this->emptyPayload('جدول live_visitors ساخته نشده است.');
        }

        $onlineSince = now()->subMinutes(self::ONLINE_MINUTES);

        $base = LiveVisitor::query()->where('last_activity_at', '>=', $onlineSince);

        $visitors = (clone $base)
            ->with(['user:id,first_name,last_name,username'])
            ->latest('last_activity_at')
            ->limit(200)
            ->get()
            ->map(function (LiveVisitor $visitor): array {
                $user = $visitor->user;

                return [
                    'id' => $visitor->id,
                    'visitor_uuid' => $visitor->visitor_uuid,
                    'registered' => (bool) $visitor->user_id,
                    'name' => $user ? trim($user->first_name . ' ' . $user->last_name) : 'کاربر مهمان',
                    'phone' => $visitor->phone ?: $user?->username,
                    'ip_address' => $visitor->ip_address,
                    'device_type' => $visitor->device_type ?: 'desktop',
                    'device_name' => $visitor->device_name,
                    'platform' => $visitor->platform,
                    'browser' => $visitor->browser,
                    'location' => $this->locationLabel($visitor),
                    'country_code' => $visitor->country_code,
                    'current_url' => $visitor->current_url,
                    'current_path' => $visitor->current_path,
                    'current_page_title' => $visitor->current_page_title,
                    'current_action' => $visitor->current_action ?: 'مشاهده سایت',
                    'product_id' => $visitor->current_product_id,
                    'product_title' => $visitor->current_product_title,
                    'page_views' => (int) $visitor->page_views,
                    'last_activity_at' => optional($visitor->last_activity_at)->toIso8601String(),
                    'last_activity_human' => optional($visitor->last_activity_at)->diffForHumans(),
                ];
            })
            ->values();

        $online = (clone $base)->count();
        $registered = (clone $base)->whereNotNull('user_id')->count();
        $mobile = (clone $base)->whereIn('device_type', ['mobile', 'tablet'])->count();

        return [
            'ok' => true,
            'generated_at' => now()->toIso8601String(),
            'online_window_minutes' => self::ONLINE_MINUTES,
            'stats' => [
                'online' => $online,
                'registered' => $registered,
                'guests' => max(0, $online - $registered),
                'mobile' => $mobile,
                'desktop' => max(0, $online - $mobile),
            ],
            'visitors' => $visitors,
            'error' => null,
        ];
    }

    private function emptyPayload(?string $message = null): array
    {
        return [
            'ok' => $message === null,
            'generated_at' => now()->toIso8601String(),
            'online_window_minutes' => self::ONLINE_MINUTES,
            'stats' => [
                'online' => 0,
                'registered' => 0,
                'guests' => 0,
                'mobile' => 0,
                'desktop' => 0,
            ],
            'visitors' => [],
            'error' => $message,
        ];
    }

    private function locationLabel(LiveVisitor $visitor): string
    {
        $parts = array_values(array_filter([
            $visitor->city,
            $visitor->region,
            $visitor->country ?: $visitor->country_code,
        ]));

        return $parts ? implode('، ', $parts) : 'نامشخص';
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\LiveVisitor;
use App\Models\LiveVisitorEvent;
use App\Support\LiveVisitorSchema;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackLiveVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldSkip($request)) {
            return $response;
        }

        try {
            LiveVisitorSchema::ensure();
            $this->track($request);
        } catch (Throwable $exception) {
            // Analytics must never break the shop.
            report($exception);
        }

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        if (!$request->isMethod('GET') || $request->expectsJson()) {
            return true;
        }

        $path = ltrim($request->path(), '/');

        return $request->is('admin*')
            || $request->is('api*')
            || $request->is('storage*')
            || $request->is('uploads*')
            || $request->is('back*')
            || $request->is('build*')
            || $request->is('css*')
            || $request->is('js*')
            || $request->is('vendor*')
            || Str::contains($path, ['payment/callback', 'gateway/callback'])
            || Str::endsWith(Str::lower($path), [
                '.css', '.js', '.map', '.jpg', '.jpeg', '.png', '.gif', '.webp',
                '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot', '.pdf', '.xml',
            ]);
    }

    private function track(Request $request): void
    {
        $visitorUuid = (string) $request->cookie('ariya_visitor_id');

        if ($visitorUuid === '') {
            $visitorUuid = (string) Str::uuid();
            Cookie::queue(cookie(
                'ariya_visitor_id',
                $visitorUuid,
                60 * 24 * 365,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'Lax'
            ));
        }

        $agent = new Agent();
        $agent->setUserAgent((string) $request->userAgent());

        $user = $request->user();
        $route = $request->route();
        $routeName = $route?->getName();
        [$productId, $productTitle] = $this->productContext($request);
        $searchQuery = $this->searchQuery($request);
        $action = $this->detectAction($request, $productTitle, $searchQuery);
        $now = now();

        $visitor = LiveVisitor::where('visitor_uuid', $visitorUuid)->first();
        $isNewPage = !$visitor
            || $visitor->current_path !== '/' . ltrim($request->path(), '/')
            || $visitor->current_product_id !== $productId;

        $attributes = [
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'user_id' => $user?->id,
            'phone' => $user?->username,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 2000, ''),
            'device_type' => $agent->isTablet() ? 'tablet' : ($agent->isMobile() ? 'mobile' : 'desktop'),
            'device_name' => $this->cleanAgentValue($agent->device()),
            'platform' => $this->cleanAgentValue($agent->platform()),
            'browser' => $this->cleanAgentValue($agent->browser()),
            'country_code' => $this->header($request, ['CF-IPCountry', 'X-Country-Code']),
            'country' => $this->header($request, ['X-Country']),
            'region' => $this->header($request, ['X-Region', 'X-Appengine-Region']),
            'city' => $this->header($request, ['X-City', 'X-Appengine-City']),
            // Deliberately use url(), not fullUrl(), so tokens and query values are not stored.
            'current_url' => $request->url(),
            'current_path' => '/' . ltrim($request->path(), '/'),
            'current_route' => $routeName,
            'current_page_title' => $this->pageTitle($request, $productTitle),
            'current_action' => $action,
            'current_product_id' => $productId,
            'current_product_title' => $productTitle,
            'referrer' => $this->safeReferrer($request),
            'last_seen_at' => $now,
            'last_activity_at' => $now,
            'metadata' => [
                'language' => $request->getPreferredLanguage(),
                'is_bot' => $agent->isRobot(),
            ],
        ];

        if ($visitor) {
            if ($isNewPage) {
                $attributes['page_views'] = ((int) $visitor->page_views) + 1;
            }
            $visitor->fill($attributes)->save();
        } else {
            $visitor = LiveVisitor::create(array_merge($attributes, [
                'visitor_uuid' => $visitorUuid,
                'first_seen_at' => $now,
                'page_views' => 1,
            ]));
        }

        if ($isNewPage) {
            LiveVisitorEvent::create([
                'live_visitor_id' => $visitor->id,
                'user_id' => $user?->id,
                'event_type' => $productId ? 'product_view' : ($searchQuery ? 'search' : 'page_view'),
                'event_name' => $action,
                'page_url' => $request->url(),
                'page_path' => '/' . ltrim($request->path(), '/'),
                'route_name' => $routeName,
                'page_title' => $this->pageTitle($request, $productTitle),
                'product_id' => $productId,
                'product_title' => $productTitle,
                'search_query' => $searchQuery,
                'payload' => null,
                'occurred_at' => $now,
            ]);
        }
    }

    /** @return array{0: ?int, 1: ?string} */
    private function productContext(Request $request): array
    {
        foreach (['product', 'price'] as $parameterName) {
            $parameter = $request->route($parameterName);

            if (is_object($parameter)) {
                $product = $parameterName === 'price' && isset($parameter->product)
                    ? $parameter->product
                    : $parameter;

                $id = isset($product->id) && is_numeric($product->id) ? (int) $product->id : null;
                $title = isset($product->title) ? trim((string) $product->title) : null;

                if ($id || $title) {
                    return [$id, $title ?: null];
                }
            }

            if (is_numeric($parameter)) {
                return [(int) $parameter, null];
            }
        }

        return [null, null];
    }

    private function searchQuery(Request $request): ?string
    {
        foreach (['q', 'query', 'search', 'keyword'] as $key) {
            $value = trim((string) $request->query($key, ''));
            if ($value !== '') {
                return Str::limit($value, 255, '');
            }
        }

        return null;
    }

    private function detectAction(Request $request, ?string $productTitle, ?string $searchQuery): string
    {
        $path = Str::lower('/' . ltrim($request->path(), '/'));

        if ($productTitle) {
            return 'مشاهده محصول: ' . Str::limit($productTitle, 180);
        }
        if ($searchQuery) {
            return 'جست‌وجوی «' . Str::limit($searchQuery, 120) . '»';
        }
        if (Str::contains($path, ['checkout', 'order/create'])) {
            return 'در حال تکمیل سفارش';
        }
        if (Str::contains($path, ['cart', 'basket'])) {
            return 'مشاهده سبد خرید';
        }
        if (Str::contains($path, ['favorite', 'wishlist'])) {
            return 'مشاهده علاقه‌مندی‌ها';
        }
        if (Str::contains($path, ['/products', '/product'])) {
            return 'مشاهده محصولات';
        }
        if ($path === '/' || $path === '') {
            return 'مشاهده صفحه اصلی';
        }

        return 'مشاهده صفحه';
    }

    private function pageTitle(Request $request, ?string $productTitle): string
    {
        if ($productTitle) {
            return Str::limit($productTitle, 255, '');
        }

        $routeName = $request->route()?->getName();
        return $routeName ? Str::limit($routeName, 255, '') : Str::limit('/' . ltrim($request->path(), '/'), 255, '');
    }

    private function cleanAgentValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' && Str::lower($value) !== 'unknown' ? Str::limit($value, 100, '') : null;
    }

    private function header(Request $request, array $names): ?string
    {
        foreach ($names as $name) {
            $value = trim((string) $request->header($name, ''));
            if ($value !== '') {
                return Str::limit($value, 100, '');
            }
        }

        return null;
    }

    private function safeReferrer(Request $request): ?string
    {
        $referrer = trim((string) $request->headers->get('referer'));
        if ($referrer === '') {
            return null;
        }

        $parts = parse_url($referrer);
        if (!is_array($parts)) {
            return null;
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';

        return Str::limit($scheme . $host . $path, 2000, '');
    }
}

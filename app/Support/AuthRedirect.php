<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AuthRedirect
{
    public const CUSTOMER_INTENDED = 'customer.url.intended';
    public const ADMIN_INTENDED = 'admin.url.intended';

    public static function isAdminRequest(Request $request): bool
    {
        $routeName = optional($request->route())->getName();

        if (is_string($routeName) && preg_match('/(^|\.)admin\./', $routeName)) {
            return true;
        }

        return self::isAdminPath($request->path());
    }

    public static function safeStorefrontUrl(Request $request, ?string $destination): ?string
    {
        $destination = self::safeLocalUrl($request, $destination);

        if ($destination === null || self::isAdminPath((string) parse_url($destination, PHP_URL_PATH))) {
            return null;
        }

        return $destination;
    }

    public static function safeAdminUrl(Request $request, ?string $destination): ?string
    {
        $destination = self::safeLocalUrl($request, $destination);

        if ($destination === null || ! self::isAdminPath((string) parse_url($destination, PHP_URL_PATH))) {
            return null;
        }

        return $destination;
    }

    public static function pullStorefrontDestination(Request $request): string
    {
        $candidates = [
            $request->session()->pull(self::CUSTOMER_INTENDED),
            $request->session()->pull('url.intended'),
        ];

        foreach ($candidates as $candidate) {
            if ($destination = self::safeStorefrontUrl($request, $candidate)) {
                return $destination;
            }
        }

        return self::storefrontHome($request);
    }

    public static function pullAdminDestination(Request $request): string
    {
        $candidates = [
            $request->session()->pull(self::ADMIN_INTENDED),
            $request->session()->pull('url.intended'),
        ];

        foreach ($candidates as $candidate) {
            if ($destination = self::safeAdminUrl($request, $candidate)) {
                return $destination;
            }
        }

        return self::adminDashboard($request);
    }

    public static function rememberAdminIntended(Request $request): void
    {
        if (! $request->isMethod('GET')) {
            return;
        }

        $destination = self::safeAdminUrl($request, $request->fullUrl());

        if ($destination !== null) {
            $request->session()->put(self::ADMIN_INTENDED, $destination);
        }
    }

    public static function storefrontHome(Request $request): string
    {
        $locale = self::pathLocale($request);
        $routeName = $locale ? $locale.'.front.index' : 'front.index';

        return Route::has($routeName) ? route($routeName) : url('/');
    }

    public static function adminLogin(Request $request): string
    {
        $locale = self::pathLocale($request);
        $routeName = $locale ? $locale.'.admin.login' : 'admin.login';

        return route(Route::has($routeName) ? $routeName : 'admin.login');
    }

    public static function adminDashboard(Request $request): string
    {
        $locale = self::pathLocale($request);
        $routeName = $locale ? $locale.'.admin.dashboard' : 'admin.dashboard';

        return route(Route::has($routeName) ? $routeName : 'admin.dashboard');
    }

    private static function safeLocalUrl(Request $request, ?string $destination): ?string
    {
        if (! is_string($destination)) {
            return null;
        }

        $destination = trim($destination);

        if (
            $destination === ''
            || preg_match('/[\x00-\x1F\x7F]/', $destination)
            || preg_match('/%(?![0-9A-Fa-f]{2})/', $destination)
            || str_contains($destination, '\\')
            || str_starts_with($destination, '//')
        ) {
            return null;
        }

        $parts = parse_url($destination);

        if ($parts === false) {
            return null;
        }

        if (isset($parts['scheme']) || isset($parts['host'])) {
            if (
                ! isset($parts['scheme'], $parts['host'])
                || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)
                || isset($parts['user'])
                || isset($parts['pass'])
                || strtolower($parts['scheme']) !== strtolower($request->getScheme())
                || strtolower($parts['host']) !== strtolower($request->getHost())
                || self::urlPort($parts) !== $request->getPort()
            ) {
                return null;
            }
        } elseif (! str_starts_with($destination, '/')) {
            return null;
        }

        return $destination;
    }

    private static function isAdminPath(string $path): bool
    {
        for ($i = 0; $i < 3; $i++) {
            $decoded = rawurldecode($path);

            if ($decoded === $path) {
                break;
            }

            $path = $decoded;
        }

        if (str_contains($path, '\\')) {
            return true;
        }

        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);
                continue;
            }

            $segments[] = strtolower($segment);
        }

        if (isset($segments[0]) && array_key_exists($segments[0], (array) config('app.locales'))) {
            array_shift($segments);
        }

        return ($segments[0] ?? null) === 'admin';
    }

    private static function pathLocale(Request $request): ?string
    {
        $locale = strtolower((string) $request->segment(1));

        return array_key_exists($locale, (array) config('app.locales')) ? $locale : null;
    }

    private static function urlPort(array $parts): int
    {
        if (isset($parts['port'])) {
            return (int) $parts['port'];
        }

        return strtolower($parts['scheme']) === 'https' ? 443 : 80;
    }
}

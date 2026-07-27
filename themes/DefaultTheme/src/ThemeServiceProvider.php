<?php

namespace Themes\DefaultTheme\src;

use App\Models\Category;
use App\Models\Link;
use App\Models\Menu;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register()
    {
        require_once(__DIR__ . '/helpers.php');

        if ($this->app['config']->get('front') === null) {
            $this->app['config']->set('front', require __DIR__ . '/../config/general.php');
        }
    }

    public function boot()
    {
        Route::group([
            'middleware' => ['web'],
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        });

        foreach (config('app.locales') as $locale => $options) {
            Route::group([
                'middleware' => ['web'],
                'prefix'     => $locale,
                'as'         => $locale . '.',
            ], function () {
                $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
            });
        }

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'front');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'front');

        if (!$this->app->runningInConsole()) {
            $this->viewComposer();
        }
    }

    private function viewComposer()
    {
        view()->composer(['front::partials.footer'], function ($view) {
            $footer_links = config('front.linkGroups', []);
            $links = Link::detectLang()->orderBy('ordering')->get();

            $view->with(compact('footer_links', 'links'));
        });

        view()->composer(['front::partials.menu.menu', 'front::partials.mobile-menu.menu'], function ($view) {
            /*
             * دسته‌بندی‌های مگامنو عمداً Forever Cache نمی‌شوند؛
             * تا افزودن، انتشار یا جابه‌جایی دسته‌ها فوراً در سایت دیده شود.
             */
            $productcats = Category::detectLang()
                ->published()
                ->whereNull('category_id')
                ->where('type', 'productcat')
                ->with([
                    'categories' => function ($query) {
                        $query->published()->orderBy('ordering');
                    },
                    'categories.categories' => function ($query) {
                        $query->published()->orderBy('ordering');
                    },
                ])
                ->orderBy('ordering')
                ->get();

            $postcats = Category::detectLang()
                ->published()
                ->where('type', 'postcat')
                ->whereNull('category_id')
                ->orderBy('ordering')
                ->get();

            $menus = Menu::detectLang()
                ->whereNull('menu_id')
                ->orderBy('ordering')
                ->get();

            $view->with(compact('productcats', 'postcats', 'menus'));
        });

        view()->composer(['front::posts.partials.sidebar'], function ($view) {
            $latest_posts = Post::detectLang()
                ->where('published', true)
                ->latest()
                ->take(6)
                ->get();

            $view->with(compact('latest_posts'));
        });

        view()->composer(['front::user.layouts.master'], function ($view) {
            $user = auth()->user();
            $random_products = Product::detectLang()
                ->published()
                ->available()
                ->inRandomOrder()
                ->limit(10)
                ->get();

            $view->with(compact('user', 'random_products'));
        });

        view()->composer([
            'front::partials.cart',
            'front::partials.checkout-sidebar',
            'front::checkout',
            'front::cart',
        ], function ($view) {
            $view->with('cart', get_cart());
        });
    }
}

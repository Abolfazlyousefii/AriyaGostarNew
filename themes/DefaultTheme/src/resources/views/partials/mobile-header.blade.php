<header class="mobile-site-header" aria-label="سربرگ موبایل">
    <div class="mobile-site-header__inner">
        <a
            href="{{ route('front.index') }}"
            class="mobile-site-header__logo"
            aria-label="{{ option('info_site_title', 'فروشگاه آریا') }}"
        >
            <img
                src="{{ theme_asset('img/ariya-mobile-logo.png') }}"
                alt="{{ option('info_site_title', 'فروشگاه آریا') }}"
                loading="eager"
                decoding="async"
                fetchpriority="high"
            >
        </a>
    </div>
</header>

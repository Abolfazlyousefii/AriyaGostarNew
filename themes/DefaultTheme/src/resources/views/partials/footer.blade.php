@php
    /*
     * فوتر مستقل از تنظیمات جدید رندر می‌شود و برای تمام داده‌ها fallback دارد.
     */
    $footerGroupsConfig = collect($footer_links ?? config('front.linkGroups', []));
    $footerLinksCollection = collect($links ?? []);

    $footerGroups = $footerGroupsConfig->take(3)->values()->map(function ($group, $index) use ($footerLinksCollection) {
        $groupKey = (int) ($group['key'] ?? ($index + 1));
        $configuredTitle = option('ariya_footer_group_' . ($index + 1) . '_title');
        $legacyTitle = $index === 0 ? option('ariya_footer_quick_title') : null;
        $defaultTitle = option('link_groups_' . $groupKey, $group['name'] ?? ('گروه ' . ($index + 1)));

        return [
            'key' => $groupKey,
            'title' => $configuredTitle ?: ($legacyTitle ?: $defaultTitle),
            'links' => $footerLinksCollection->where('link_group_id', $groupKey),
        ];
    });

    if ($footerGroups->isEmpty()) {
        $quickGroupKey = (int) (option('ariya_footer_quick_group_key') ?: 1);
        $footerGroups = collect([[
            'key' => $quickGroupKey,
            'title' => option('ariya_footer_quick_title') ?: 'دسترسی سریع',
            'links' => $footerLinksCollection->where('link_group_id', $quickGroupKey),
        ]]);
    }

    // گروه اول همیشه باقی می‌ماند؛ گروه دوم و سوم فقط در صورت داشتن لینک نمایش داده می‌شوند.
    $footerGroups = $footerGroups->filter(function ($group, $index) {
        return $index === 0 || $group['links']->isNotEmpty();
    })->values();

    $footerLogo = option('ariya_footer_logo') ?: option('info_logo', theme_asset('img/logo.png'));
    $siteTitle = option('info_site_title') ?: 'آریا جانبی';
    $footerBrandline = option('ariya_footer_brandline')
        ?: 'آریا جانبی؛ انتخاب مطمئن برای خرید لوازم جانبی موبایل با کیفیت و قیمت مناسب.';
    $footerAboutTitle = option('ariya_footer_about_title') ?: 'درباره آریا جانبی';
    $footerAboutText = option('ariya_footer_about_text')
        ?: 'فروشگاه آریا جانبی با تمرکز بر اصالت کالا، قیمت منصفانه و پشتیبانی پاسخ‌گو، تجربه‌ای ساده و مطمئن برای خرید لوازم جانبی موبایل فراهم می‌کند.';

    $footerAddress = option('ariya_footer_address');
    $footerEmail = option('ariya_footer_email');
    $footerPhone = option('ariya_footer_whatsapp_phone');
    $footerWarrantyPhone = option('ariya_footer_warranty_phone');

    $addressLink = option('ariya_footer_address_link');
    $whatsappLink = option('ariya_footer_whatsapp_link');
    $warrantyLink = option('ariya_footer_warranty_link');

    $footerInstagram = option('ariya_footer_instagram_link');
    $footerTelegram = option('ariya_footer_telegram_link');
    $footerWhatsappSocial = option('ariya_footer_whatsapp_social_link');

    $footerEnamad = option('ariya_footer_enamad_code') ?: option('info_enamad');
    $footerSamandehi = option('ariya_footer_samandehi_code');

    $footerFeatures = [
        [
            'title' => option('ariya_footer_feature_1_title') ?: 'پشتیبانی تخصصی',
            'text' => option('ariya_footer_feature_1_text') ?: 'پیش و پس از خرید کنار شما هستیم',
            'icon' => 'support',
        ],
        [
            'title' => option('ariya_footer_feature_2_title') ?: 'ارسال سریع',
            'text' => option('ariya_footer_feature_2_text') ?: 'ارسال سفارش به سراسر کشور',
            'icon' => 'delivery',
        ],
        [
            'title' => option('ariya_footer_feature_3_title') ?: 'قیمت رقابتی',
            'text' => option('ariya_footer_feature_3_text') ?: 'خرید مطمئن با قیمت منصفانه',
            'icon' => 'price',
        ],
        [
            'title' => option('ariya_footer_feature_4_title') ?: 'تضمین کیفیت',
            'text' => option('ariya_footer_feature_4_text') ?: 'کنترل کیفیت و ضمانت اصالت کالا',
            'icon' => 'quality',
        ],
    ];
@endphp

<footer id="bgFooter" class="ariya-vue-like-footer ariya-combined-footer mt-5 mb-5 mb-md-0" dir="rtl" aria-label="فوتر سایت">
    <style>
        .ariya-combined-footer,
        .ariya-combined-footer * {
            box-sizing: border-box;
        }

        .ariya-combined-footer {
            --af-primary: #0a8acb;
            --af-dark: #22364b;
            --af-text: #667788;
            --af-border: #e8edf2;
            --af-soft: #f7fafc;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100%;
            margin: 28px 0 0 !important;
            padding: 0 0 12px;
            clear: both;
            color: var(--af-dark);
            background: transparent !important;
        }

        .ariya-combined-footer a {
            text-decoration: none;
        }

        /* هم‌اندازه با کانتینر اصلی سکشن‌های سایت */
        .ariya-combined-footer .af-container {
            width: calc(100% - 30px);
            max-width: 1400px;
            margin: 0 auto;
        }

        .ariya-combined-footer .af-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            margin-bottom: 10px;
            border: 1px solid var(--af-border);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        .ariya-combined-footer .af-feature {
            flex: 1 1 220px;
            min-width: 0;
            min-height: 58px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 12px;
            background: #fff;
            border-left: 1px solid var(--af-border);
        }

        .ariya-combined-footer .af-feature:last-child {
            border-left: 0;
        }

        .ariya-combined-footer .af-feature-icon {
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: var(--af-primary);
            background: #eef8fc;
        }

        .ariya-combined-footer .af-feature-icon svg,
        .ariya-combined-footer .af-icon svg,
        .ariya-combined-footer .af-social svg,
        .ariya-combined-footer .af-summary svg {
            width: 17px;
            height: 17px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .ariya-combined-footer .af-feature h3 {
            margin: 0 0 1px;
            color: var(--af-dark);
            font-size: 12px;
            font-weight: 600;
            line-height: 1.65;
        }

        .ariya-combined-footer .af-feature p {
            margin: 0;
            color: #8a97a4;
            font-size: 9.5px;
            line-height: 1.55;
        }

        .ariya-combined-footer .af-panel {
            border: 1px solid var(--af-border);
            border-radius: 10px;
            overflow: hidden;
            background: #fff !important;
            box-shadow: none;
        }

        .ariya-combined-footer .af-brandbar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px 18px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--af-border);
        }

        .ariya-combined-footer .af-logo {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
        }

        .ariya-combined-footer .af-logo img {
            display: block;
            width: auto;
            max-width: 118px;
            height: auto;
            max-height: 46px;
            object-fit: contain;
        }

        .ariya-combined-footer .af-brandline {
            flex: 1 1 360px;
            min-width: 0;
            margin: 0;
            color: var(--af-text);
            font-size: 11.5px;
            line-height: 1.8;
        }

        .ariya-combined-footer .af-socials {
            flex: 0 1 auto;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .ariya-combined-footer .af-social {
            height: 31px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 0 8px;
            border: 1px solid var(--af-border);
            border-radius: 7px;
            color: #637487;
            background: #fff;
            font-size: 10.5px;
            transition: .18s ease;
        }

        .ariya-combined-footer .af-social:hover {
            color: var(--af-primary);
            border-color: #c8e3ee;
            background: #f6fcfe;
        }

        .ariya-combined-footer .af-main {
            display: flex;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 18px 24px;
            padding: 18px 16px;
        }

        .ariya-combined-footer .af-about {
            flex: 2 1 390px;
            min-width: 280px;
        }

        .ariya-combined-footer .af-title {
            margin: 0;
            color: var(--af-dark);
            font-size: 12.5px;
            font-weight: 650;
            line-height: 1.8;
        }

        .ariya-combined-footer .af-about-text {
            margin-top: 6px;
            color: var(--af-text);
            font-size: 11px;
            line-height: 1.9;
        }

        .ariya-combined-footer .af-about-text p {
            margin-bottom: 5px;
        }

        .ariya-combined-footer .af-about-text p:last-child {
            margin-bottom: 0;
        }

        .ariya-combined-footer .af-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 8px;
        }

        .ariya-combined-footer .af-tags span {
            min-height: 23px;
            display: inline-flex;
            align-items: center;
            padding: 0 7px;
            border-radius: 6px;
            color: #65788a;
            background: var(--af-soft);
            font-size: 9.5px;
        }

        .ariya-combined-footer .af-address-simple {
            display: block;
            margin-top: 9px;
            color: #111827;
            font-size: 9.5px;
            font-weight: 400;
            line-height: 1.85;
            overflow-wrap: anywhere;
        }

        .ariya-combined-footer a.af-address-simple:hover {
            color: var(--af-primary);
        }

        /* لینک‌ها در دسکتاپ مستقیماً داخل فلکس اصلی قرار می‌گیرند. */
        .ariya-combined-footer .af-links {
            display: contents;
        }

        .ariya-combined-footer .af-link-group {
            flex: 1 1 125px;
            min-width: 110px;
        }

        .ariya-combined-footer .af-link-group > :not(summary) {
            display: block !important;
        }

        .ariya-combined-footer .af-summary {
            width: 100%;
            min-height: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 7px;
            list-style: none;
            color: var(--af-dark);
            cursor: default;
        }

        .ariya-combined-footer .af-summary::-webkit-details-marker {
            display: none;
        }

        .ariya-combined-footer .af-summary svg {
            display: none;
        }

        .ariya-combined-footer .af-link-list {
            margin: 5px 0 0;
            padding: 0;
            list-style: none;
        }

        .ariya-combined-footer .af-link-list li + li {
            margin-top: 3px;
        }

        .ariya-combined-footer .af-link-list a {
            display: inline-block;
            color: #728090;
            font-size: 10.5px;
            line-height: 1.7;
            transition: .18s ease;
        }

        .ariya-combined-footer .af-link-list a:hover {
            color: var(--af-primary);
            transform: translateX(-1px);
        }

        .ariya-combined-footer .af-empty {
            margin: 5px 0 0;
            color: #a1aab3;
            font-size: 9px;
            line-height: 1.65;
        }

        .ariya-combined-footer .af-license-wrap {
            flex: 0 1 230px;
            min-width: 190px;
        }

        .ariya-combined-footer .af-license {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 7px;
            padding: 0;
            border: 0;
            border-radius: 0;
            text-align: right;
            background: transparent;
        }

        .ariya-combined-footer .af-license-subtitle {
            margin: 0;
            color: #a2abb4;
            font-size: 7px;
            letter-spacing: .8px;
        }

        .ariya-combined-footer .af-license-items {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .ariya-combined-footer .af-license-item {
            width: 64px;
            min-height: 66px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            border: 1px solid var(--af-border);
            border-radius: 7px;
            background: #fff;
        }

        .ariya-combined-footer .af-license-item img,
        .ariya-combined-footer .af-license-item iframe {
            display: block;
            width: auto !important;
            max-width: 56px !important;
            height: auto !important;
            max-height: 58px !important;
            object-fit: contain;
        }

        .ariya-combined-footer .af-license-empty {
            color: #a1aab3;
            font-size: 9px;
            line-height: 1.65;
        }

        .ariya-combined-footer .af-license-contact {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 3px;
            margin: 0 0 7px;
            padding: 0 0 7px;
            border-bottom: 1px solid #eef2f5;
        }

        .ariya-combined-footer .af-license-contact-row {
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 2px 0;
            color: #58697a;
            font-size: 9px;
            line-height: 1.65;
        }

        .ariya-combined-footer .af-license-contact-row span {
            flex: 0 0 auto;
            color: #909ba6;
            font-size: 8px;
        }

        .ariya-combined-footer .af-license-contact-row strong {
            min-width: 0;
            color: #263746;
            font-size: 9px;
            font-weight: 500;
            text-align: left;
            overflow-wrap: anywhere;
        }

        .ariya-combined-footer a.af-license-contact-row:hover strong {
            color: var(--af-primary);
        }

        .ariya-combined-footer .af-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 5px 14px;
            padding: 8px 16px;
            border-top: 1px solid var(--af-border);
            color: #8a96a2;
            font-size: 9.5px;
            line-height: 1.7;
        }

        .ariya-combined-footer .af-bottom p,
        .ariya-combined-footer .af-bottom span {
            margin: 0;
        }

        .ariya-combined-footer .af-bottom a {
            color: var(--af-primary);
        }

        @media (max-width: 900px) {
            .ariya-combined-footer .af-feature {
                flex-basis: 50%;
            }

            .ariya-combined-footer .af-feature:nth-child(2) {
                border-left: 0;
            }

            .ariya-combined-footer .af-feature:nth-child(-n+2) {
                border-bottom: 1px solid var(--af-border);
            }

            .ariya-combined-footer .af-about {
                flex-basis: 100%;
            }
        }

        @media (max-width: 767px) {
            .ariya-combined-footer {
                margin-top: 22px !important;
                padding-bottom: 76px;
            }

            .ariya-combined-footer .af-container {
                width: calc(100% - 16px);
            }

            .ariya-combined-footer .af-feature {
                flex: 1 1 50%;
                min-height: 53px;
                padding: 8px;
            }

            .ariya-combined-footer .af-feature-icon {
                width: 29px;
                height: 29px;
                flex-basis: 29px;
            }

            .ariya-combined-footer .af-feature h3 {
                font-size: 10.5px;
            }

            .ariya-combined-footer .af-feature p {
                font-size: 8.3px;
            }

            .ariya-combined-footer .af-brandbar {
                gap: 8px 12px;
                padding: 11px 12px;
            }

            .ariya-combined-footer .af-logo img {
                max-width: 95px;
                max-height: 39px;
            }

            .ariya-combined-footer .af-brandline {
                flex-basis: calc(100% - 115px);
                font-size: 10px;
            }

            .ariya-combined-footer .af-socials {
                flex-basis: 100%;
            }

            .ariya-combined-footer .af-social {
                height: 29px;
                font-size: 9.5px;
            }

            .ariya-combined-footer .af-main {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: start;
                gap: 8px;
                padding: 14px 12px;
            }

            .ariya-combined-footer .af-about {
                grid-column: 1 / -1;
                min-width: 0;
            }

            .ariya-combined-footer .af-links {
                min-width: 0;
                display: flex;
                flex-direction: column;
                flex-wrap: nowrap;
                gap: 6px;
            }

            .ariya-combined-footer .af-link-group {
                flex: 0 0 auto;
                width: 100%;
                min-width: 0;
                border: 1px solid var(--af-border);
                border-radius: 7px;
                overflow: hidden;
                background: #fff;
            }

            .ariya-combined-footer .af-link-group > :not(summary) {
                display: none !important;
            }

            .ariya-combined-footer .af-link-group[open] > :not(summary) {
                display: block !important;
            }

            .ariya-combined-footer .af-summary {
                min-height: 38px;
                padding: 0 9px;
                cursor: pointer;
            }

            .ariya-combined-footer .af-summary svg {
                display: block;
                width: 14px;
                height: 14px;
                transition: transform .18s ease;
            }

            .ariya-combined-footer details[open] .af-summary svg {
                transform: rotate(180deg);
            }

            .ariya-combined-footer .af-link-list,
            .ariya-combined-footer .af-empty {
                margin: 0;
                padding: 5px 10px 9px;
                border-top: 1px solid #f0f3f5;
            }

            .ariya-combined-footer .af-license-wrap {
                width: 100%;
                min-width: 0;
            }

            .ariya-combined-footer .af-license {
                width: 100%;
                padding: 9px;
                border: 1px solid var(--af-border);
                border-radius: 7px;
            }

            .ariya-combined-footer .af-license-contact-row {
                gap: 4px;
                font-size: 8.2px;
            }

            .ariya-combined-footer .af-license-contact-row span {
                font-size: 7.5px;
            }

            .ariya-combined-footer .af-license-contact-row strong {
                font-size: 8px;
                line-height: 1.45;
            }

            .ariya-combined-footer .af-bottom {
                justify-content: center;
                padding: 8px 10px;
                text-align: center;
            }
        }

        @media (max-width: 440px) {
            .ariya-combined-footer .af-feature {
                flex-basis: 100%;
                border-left: 0;
                border-bottom: 1px solid var(--af-border);
            }

            .ariya-combined-footer .af-feature:last-child {
                border-bottom: 0;
            }

        }
    </style>

    <div class="af-container">
        <section class="af-features" aria-label="مزیت‌های فروشگاه">
            @foreach($footerFeatures as $feature)
                <article class="af-feature">
                    <span class="af-feature-icon" aria-hidden="true">
                        @if($feature['icon'] === 'support')
                            <svg viewBox="0 0 24 24"><path d="M4 13v-2a8 8 0 0 1 16 0v2"></path><path d="M4 13h2a2 2 0 0 1 2 2v3H6a2 2 0 0 1-2-2v-3Z"></path><path d="M20 13h-2a2 2 0 0 0-2 2v3h2a2 2 0 0 0 2-2v-3Z"></path><path d="M16 20h-3"></path></svg>
                        @elseif($feature['icon'] === 'delivery')
                            <svg viewBox="0 0 24 24"><path d="M3 7h11v10H3z"></path><path d="M14 10h4l3 3v4h-7z"></path><circle cx="7" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle></svg>
                        @elseif($feature['icon'] === 'price')
                            <svg viewBox="0 0 24 24"><path d="M20 13 11 22l-9-9V4h9l9 9Z"></path><circle cx="7" cy="9" r="1.5"></circle></svg>
                        @else
                            <svg viewBox="0 0 24 24"><path d="m12 3 7 3v5c0 4.4-2.8 8.4-7 10-4.2-1.6-7-5.6-7-10V6l7-3Z"></path><path d="m9 12 2 2 4-4"></path></svg>
                        @endif
                    </span>
                    <div>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['text'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="af-panel footer footer-2 text-dark">
            <div class="af-brandbar">
                <a class="af-logo" href="{{ route('front.index') }}" aria-label="{{ $siteTitle }}">
                    <img src="{{ $footerLogo }}" alt="{{ $siteTitle }}" loading="lazy" decoding="async">
                </a>

                <p class="af-brandline">{{ $footerBrandline }}</p>

                <div class="af-socials" aria-label="شبکه‌های اجتماعی">
                    @if($footerInstagram)
                        <a class="af-social" href="{{ $footerInstagram }}" target="_blank" rel="nofollow noopener" aria-label="اینستاگرام">
                            <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="5"></rect><circle cx="12" cy="12" r="3.4"></circle><circle cx="17" cy="7" r=".8" fill="currentColor" stroke="none"></circle></svg>
                            <span>اینستاگرام</span>
                        </a>
                    @endif

                    @if($footerWhatsappSocial)
                        <a class="af-social" href="{{ $footerWhatsappSocial }}" target="_blank" rel="nofollow noopener" aria-label="واتساپ">
                            <svg viewBox="0 0 24 24"><path d="M20 11.5a8 8 0 0 1-11.8 7L4 20l1.5-4A8 8 0 1 1 20 11.5Z"></path><path d="M9.2 8.5c.3 2.2 2.1 4 4.3 4.7l1.2-1.1 1.8.8c.2.1.3.3.2.5-.3 1-1 1.6-2.1 1.5-2.7-.2-6.8-3.2-7.4-6.7-.2-1 .4-1.8 1.3-2.2.2-.1.4 0 .5.2l.9 1.8-.7 1Z"></path></svg>
                            <span>واتساپ</span>
                        </a>
                    @endif

                    @if($footerTelegram)
                        <a class="af-social" href="{{ $footerTelegram }}" target="_blank" rel="nofollow noopener" aria-label="تلگرام">
                            <svg viewBox="0 0 24 24"><path d="M21 4 3.8 10.8c-.8.3-.8 1.4.1 1.6l4.3 1.2 1.7 5.1c.3.8 1.3 1 1.8.3l2.4-3.1 4.6 3.3c.7.5 1.6.1 1.7-.8L22 5.1c.1-.8-.4-1.3-1-1.1Z"></path><path d="M8.3 13.5 17.8 8"></path></svg>
                            <span>تلگرام</span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="af-main">
                <section class="af-about" aria-label="درباره فروشگاه">
                    <h2 class="af-title">{{ $footerAboutTitle }}</h2>
                    <div class="af-about-text">{!! $footerAboutText !!}</div>

                    <div class="af-tags" aria-label="خدمات فروشگاه">
                        <span>ضمانت اصالت</span>
                        <span>ارسال سراسری</span>
                        <span>پشتیبانی خرید</span>
                    </div>

                    @if($footerAddress)
                        @if($addressLink)
                            <a class="af-address-simple" href="{{ $addressLink }}" target="_blank" rel="noopener">
                                {!! $footerAddress !!}
                            </a>
                        @else
                            <div class="af-address-simple">{!! $footerAddress !!}</div>
                        @endif
                    @endif
                </section>

                <nav class="af-links" aria-label="لینک‌های فوتر">
                    @foreach($footerGroups as $group)
                        <details class="af-link-group" {{ $loop->first ? 'open' : '' }}>
                            <summary class="af-summary">
                                <span class="af-title">{{ $group['title'] }}</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                            </summary>

                            @if($group['links']->isNotEmpty())
                                <ul class="af-link-list">
                                    @foreach($group['links'] as $link)
                                        @php
                                            $linkUrl = (string) $link->link;
                                            $isExternal = str_starts_with($linkUrl, 'http://') || str_starts_with($linkUrl, 'https://');
                                        @endphp
                                        <li>
                                            <a href="{{ $linkUrl }}" target="{{ $isExternal ? '_blank' : '_self' }}" @if($isExternal) rel="noopener" @endif>
                                                {{ $link->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="af-empty">لینک‌های این بخش از پنل مدیریت قابل تنظیم است.</p>
                            @endif
                        </details>
                    @endforeach
                </nav>

                <aside class="af-license-wrap" aria-label="مجوزهای فروشگاه">
                    <div class="af-license">
                        @if($footerPhone || $footerEmail || $footerWarrantyPhone || $footerWhatsappSocial)
                            <div class="af-license-contact" aria-label="راه‌های ارتباطی">
                                @if($footerPhone)
                                    <a class="af-license-contact-row" href="tel:{{ preg_replace('/[^0-9+]/', '', $footerPhone) }}">
                                        <span>تلفن</span>
                                        <strong dir="ltr">{{ $footerPhone }}</strong>
                                    </a>
                                @endif

                                @if($footerPhone && ($whatsappLink || $footerWhatsappSocial))
                                    <a class="af-license-contact-row" href="{{ $whatsappLink ?: $footerWhatsappSocial }}" target="_blank" rel="nofollow noopener">
                                        <span>واتساپ</span>
                                        <strong dir="ltr">{{ $footerPhone }}</strong>
                                    </a>
                                @elseif($footerWhatsappSocial)
                                    <a class="af-license-contact-row" href="{{ $footerWhatsappSocial }}" target="_blank" rel="nofollow noopener">
                                        <span>واتساپ</span>
                                        <strong>ارتباط در واتساپ</strong>
                                    </a>
                                @endif

                                @if($footerEmail)
                                    <a class="af-license-contact-row" href="mailto:{{ $footerEmail }}">
                                        <span>ایمیل</span>
                                        <strong dir="ltr">{{ $footerEmail }}</strong>
                                    </a>
                                @endif

                                @if($footerWarrantyPhone)
                                    <a class="af-license-contact-row" href="{{ $warrantyLink ?: ('tel:' . preg_replace('/[^0-9+]/', '', $footerWarrantyPhone)) }}">
                                        <span>گارانتی</span>
                                        <strong dir="ltr">{{ $footerWarrantyPhone }}</strong>
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if($footerEnamad || $footerSamandehi)
                            <div class="af-license-items">
                                @if($footerEnamad)
                                    <div class="af-license-item">{!! $footerEnamad !!}</div>
                                @endif
                                @if($footerSamandehi)
                                    <div class="af-license-item">{!! $footerSamandehi !!}</div>
                                @endif
                            </div>
                        @else
                            <div class="af-license-empty">کد نماد اعتماد از تنظیمات قالب قابل ثبت است.</div>
                        @endif
                    </div>
                </aside>
            </div>

            <div class="af-bottom copyright">
                <p>{!! option('info_footer_text') ?: 'تمام حقوق مادی و معنوی این وب‌سایت برای آریا جانبی محفوظ است.' !!}</p>
                <span>طراحی و توسعه توسط تیم IT آریا گستر</span>
            </div>
        </div>
    </div>
</footer>

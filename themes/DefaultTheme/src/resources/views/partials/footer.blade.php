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

    $footerLogo = media_url(
    option('ariya_footer_logo') ?: option('info_logo'),
    theme_asset('img/logo.png')
);
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
    /* ARIYA_FOOTER_LIGHT_V2 */

    .ariya-combined-footer,
    .ariya-combined-footer * {
        box-sizing: border-box;
    }

    .ariya-combined-footer {
        --af-primary: #118fd2;
        --af-primary-dark: #0872b6;
        --af-dark: #18344d;
        --af-text: #607589;
        --af-muted: #8aa0b1;
        --af-border: #dfeaf1;
        --af-soft: #f4f9fc;

        position: relative;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;

        width: 100%;
        margin: 38px 0 0 !important;
        padding: 42px 0 18px;
        clear: both;
        overflow: hidden;

        color: var(--af-text);

        background:
            radial-gradient(
                circle at 10% 12%,
                rgba(17,143,210,.10),
                transparent 28%
            ),
            radial-gradient(
                circle at 90% 82%,
                rgba(17,143,210,.07),
                transparent 25%
            ),
            linear-gradient(
                180deg,
                #f7fbfe 0%,
                #eef6fb 100%
            ) !important;

        border-top: 1px solid #e1edf4;
    }

    .ariya-combined-footer::before {
        content: "";
        position: absolute;
        top: -170px;
        left: 50%;

        width: 760px;
        height: 330px;

        transform: translateX(-50%);

        pointer-events: none;

        background:
            radial-gradient(
                ellipse at center,
                rgba(16,145,211,.08),
                transparent 70%
            );
    }

    .ariya-combined-footer a {
        text-decoration: none !important;
    }

    .ariya-combined-footer .af-container {
        position: relative;
        z-index: 2;

        width: calc(100% - 36px);
        max-width: 1400px;

        margin: 0 auto;
    }

    /* Features */

    .ariya-combined-footer .af-features {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));

        gap: 12px;

        margin: 0 0 16px;
        padding: 0;

        border: 0;
        background: transparent;
        overflow: visible;
    }

    .ariya-combined-footer .af-feature {
        min-width: 0;
        min-height: 92px;

        display: flex;
        align-items: center;

        gap: 13px;

        padding: 16px 17px;

        border: 1px solid #dce9f1;
        border-radius: 16px;

        background:
            linear-gradient(
                145deg,
                #ffffff,
                #f7fbfd
            );

        box-shadow:
            0 8px 24px rgba(36,77,107,.07);

        transition:
            transform .22s ease,
            border-color .22s ease,
            box-shadow .22s ease;
    }

    .ariya-combined-footer .af-feature:hover {
        transform: translateY(-4px);

        border-color: #b9dff1;

        box-shadow:
            0 15px 34px rgba(17,111,166,.12);
    }

    .ariya-combined-footer .af-feature-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        border-radius: 14px;

        color: #fff;

        background:
            linear-gradient(
                145deg,
                #25a8eb,
                #0a79c1
            );

        box-shadow:
            0 8px 18px rgba(9,126,196,.22);
    }

    .ariya-combined-footer .af-feature-icon svg,
    .ariya-combined-footer .af-icon svg,
    .ariya-combined-footer .af-social svg,
    .ariya-combined-footer .af-summary svg {
        width: 21px;
        height: 21px;

        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .ariya-combined-footer .af-feature h3 {
        margin: 0 0 4px;

        color: #17344d;

        font-size: 13px;
        font-weight: 700;
        line-height: 1.7;
    }

    .ariya-combined-footer .af-feature p {
        margin: 0;

        color: #8498a8;

        font-size: 10.5px;
        line-height: 1.7;
    }

    /* Main panel */

    .ariya-combined-footer .af-panel {
        overflow: hidden;

        border: 1px solid #dbe8ef;
        border-radius: 22px;

        color: var(--af-text) !important;

        background: #fff !important;

        box-shadow:
            0 18px 55px rgba(46,86,113,.10);
    }

    .ariya-combined-footer .af-panel.text-dark {
        color: var(--af-text) !important;
    }

    /* Brand */

    .ariya-combined-footer .af-brandbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;

        gap: 14px 22px;

        padding: 22px 24px;

        border-bottom: 1px solid #e4edf3;

        background:
            linear-gradient(
                90deg,
                #f3f9fc,
                #ffffff
            );
    }

    .ariya-combined-footer .af-logo {
        flex: 0 0 auto;

        display: inline-flex;
        align-items: center;

        padding: 9px 13px;

        border: 1px solid #d8e5ed;
        border-radius: 13px;

        background: #fff;

        box-shadow:
            0 6px 18px rgba(56,91,117,.08);
    }

    .ariya-combined-footer .af-logo img {
        display: block;

        width: auto;
        max-width: 125px;

        height: auto;
        max-height: 48px;

        object-fit: contain;
    }

    .ariya-combined-footer .af-brandline {
        flex: 1 1 360px;
        min-width: 0;

        margin: 0;

        color: #607589;

        font-size: 12px;
        line-height: 1.95;
    }

    /* Social */

    .ariya-combined-footer .af-socials {
        flex: 0 1 auto;

        display: flex;
        align-items: center;
        flex-wrap: wrap;

        gap: 7px;
    }

    .ariya-combined-footer .af-social {
        height: 37px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        gap: 6px;

        padding: 0 11px;

        border: 1px solid #dce8ef;
        border-radius: 10px;

        color: #567287;

        background: #fff;

        font-size: 10.5px;

        transition:
            color .2s ease,
            border-color .2s ease,
            background .2s ease,
            transform .2s ease;
    }

    .ariya-combined-footer .af-social svg {
        width: 17px;
        height: 17px;
    }

    .ariya-combined-footer .af-social:hover {
        color: #fff;

        border-color: var(--af-primary);

        background:
            linear-gradient(
                135deg,
                #1ba1e4,
                #0878bd
            );

        transform: translateY(-2px);
    }

    /* Main */

    .ariya-combined-footer .af-main {
        display: flex;
        align-items: flex-start;
        flex-wrap: wrap;

        gap: 28px 32px;

        padding: 30px 25px 26px;
    }

    .ariya-combined-footer .af-about {
        flex: 2.15 1 390px;
        min-width: 280px;
    }

    .ariya-combined-footer .af-title {
        position: relative;

        display: inline-block;

        margin: 0 0 4px;
        padding-bottom: 8px;

        color: #17344d;

        font-size: 13px;
        font-weight: 700;
        line-height: 1.8;
    }

    .ariya-combined-footer .af-title::after {
        content: "";

        position: absolute;
        right: 0;
        bottom: 0;

        width: 30px;
        height: 2px;

        border-radius: 999px;

        background:
            linear-gradient(
                90deg,
                #149ee3,
                #a9d8ee
            );
    }

    .ariya-combined-footer .af-about-text {
        margin-top: 8px;

        color: #607589;

        font-size: 11.5px;
        line-height: 2.05;
    }

    .ariya-combined-footer .af-about-text p {
        margin-bottom: 7px;
    }

    .ariya-combined-footer .af-about-text p:last-child {
        margin-bottom: 0;
    }

    /* Tags */

    .ariya-combined-footer .af-tags {
        display: flex;
        flex-wrap: wrap;

        gap: 6px;

        margin-top: 13px;
    }

    .ariya-combined-footer .af-tags span {
        min-height: 28px;

        display: inline-flex;
        align-items: center;

        padding: 0 9px;

        border: 1px solid #dbe9f1;
        border-radius: 8px;

        color: #5d788c;

        background: #f4f9fc;

        font-size: 9.5px;
    }

    /* Address */

    .ariya-combined-footer .af-address-simple {
        position: relative;

        display: block;

        margin-top: 15px;
        padding: 11px 13px 11px 38px;

        border: 1px solid #dce9f1;
        border-radius: 10px;

        color: #38546b;

        background: #f7fbfd;

        font-size: 10px;
        font-weight: 400;
        line-height: 1.9;

        overflow-wrap: anywhere;

        transition:
            background .2s ease,
            border-color .2s ease;
    }

    .ariya-combined-footer .af-address-simple::before {
        content: "⌖";

        position: absolute;
        left: 13px;
        top: 50%;

        transform: translateY(-50%);

        color: #159fe3;

        font-size: 20px;
        line-height: 1;
    }

    .ariya-combined-footer a.af-address-simple:hover {
        color: #0874b9;

        border-color: #b8deef;

        background: #eef8fc;
    }

    /* Links */

    .ariya-combined-footer .af-links {
        display: contents;
    }

    .ariya-combined-footer .af-link-group {
        flex: 1 1 130px;
        min-width: 120px;
    }

    .ariya-combined-footer .af-link-group > :not(summary) {
        display: block !important;
    }

    .ariya-combined-footer .af-summary {
        width: 100%;
        min-height: 31px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 7px;

        list-style: none;

        color: #17344d;

        cursor: default;
    }

    .ariya-combined-footer .af-summary::-webkit-details-marker {
        display: none;
    }

    .ariya-combined-footer .af-summary svg {
        display: none;
    }

    .ariya-combined-footer .af-link-list {
        margin: 9px 0 0;
        padding: 0;

        list-style: none;
    }

    .ariya-combined-footer .af-link-list li + li {
        margin-top: 5px;
    }

    .ariya-combined-footer .af-link-list a {
        position: relative;

        display: inline-flex;
        align-items: center;

        padding-right: 12px;

        color: #6b8193;

        font-size: 10.5px;
        line-height: 1.8;

        transition:
            color .18s ease,
            transform .18s ease;
    }

    .ariya-combined-footer .af-link-list a::before {
        content: "";

        position: absolute;
        right: 0;

        width: 4px;
        height: 4px;

        border-radius: 50%;

        background: #1a9fe1;
    }

    .ariya-combined-footer .af-link-list a:hover {
        color: #0876ba;

        transform: translateX(-3px);
    }

    .ariya-combined-footer .af-empty {
        margin: 8px 0 0;

        color: #9aacb9;

        font-size: 9px;
        line-height: 1.75;
    }

    /* Contact / license */

    .ariya-combined-footer .af-license-wrap {
        flex: 0 1 250px;
        min-width: 215px;
    }

    .ariya-combined-footer .af-license {
        width: 100%;

        display: flex;
        flex-direction: column;
        align-items: stretch;

        gap: 10px;

        padding: 13px;

        border: 1px solid #dce9f1;
        border-radius: 14px;

        text-align: right;

        background:
            linear-gradient(
                145deg,
                #f9fcfe,
                #f2f8fb
            );
    }

    .ariya-combined-footer .af-license-contact {
        width: 100%;

        display: flex;
        flex-direction: column;

        gap: 5px;

        margin: 0;
        padding: 0 0 10px;

        border-bottom: 1px solid #e1ebf1;
    }

    .ariya-combined-footer .af-license-contact-row {
        min-width: 0;
        min-height: 34px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 8px;

        padding: 5px 8px;

        border: 1px solid transparent;
        border-radius: 8px;

        color: #718798;

        font-size: 9px;
        line-height: 1.65;

        background: #fff;

        transition:
            background .18s ease,
            border-color .18s ease;
    }

    .ariya-combined-footer .af-license-contact-row:hover {
        border-color: #cae4f1;

        background: #f1f9fd;
    }

    .ariya-combined-footer .af-license-contact-row span {
        flex: 0 0 auto;

        color: #8ea0ae;

        font-size: 8px;
    }

    .ariya-combined-footer .af-license-contact-row strong {
        min-width: 0;

        color: #314e65;

        font-size: 9px;
        font-weight: 500;
        text-align: left;

        overflow-wrap: anywhere;
    }

    .ariya-combined-footer a.af-license-contact-row:hover strong {
        color: #0876ba;
    }

    /* License logos */

    .ariya-combined-footer .af-license-items {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: wrap;

        gap: 8px;
    }

    .ariya-combined-footer .af-license-item {
        width: 76px;
        min-height: 78px;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 7px;

        border: 1px solid #d8e6ee;
        border-radius: 11px;

        background: #fff;

        box-shadow:
            0 7px 18px rgba(48,83,108,.08);

        transition:
            transform .2s ease,
            box-shadow .2s ease;
    }

    .ariya-combined-footer .af-license-item:hover {
        transform: translateY(-3px);

        box-shadow:
            0 12px 24px rgba(35,89,126,.12);
    }

    .ariya-combined-footer .af-license-item img,
    .ariya-combined-footer .af-license-item iframe {
        display: block;

        width: auto !important;
        max-width: 63px !important;

        height: auto !important;
        max-height: 65px !important;

        object-fit: contain;
    }

    .ariya-combined-footer .af-license-empty {
        color: #98aab7;

        font-size: 9px;
        line-height: 1.7;
    }

    /* Bottom */

    .ariya-combined-footer .af-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;

        gap: 7px 16px;

        padding: 13px 24px;

        border-top: 1px solid #e3edf3;

        color: #8395a3;

        background: #f7fafc;

        font-size: 9.5px;
        line-height: 1.8;
    }

    .ariya-combined-footer .af-bottom p,
    .ariya-combined-footer .af-bottom span {
        margin: 0;
    }

    .ariya-combined-footer .af-bottom a {
        color: #128fd1;
    }

    @media (max-width: 1050px) {

        .ariya-combined-footer .af-features {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ariya-combined-footer .af-about {
            flex-basis: 100%;
        }

        .ariya-combined-footer .af-license-wrap {
            flex-basis: 260px;
        }
    }

    @media (max-width: 767px) {

        .ariya-combined-footer {
            margin-top: 28px !important;
            padding-top: 25px;
            padding-bottom: 86px;
        }

        .ariya-combined-footer .af-container {
            width: calc(100% - 16px);
        }

        .ariya-combined-footer .af-features {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 7px;
            margin-bottom: 9px;
        }

        .ariya-combined-footer .af-feature {
            min-height: 76px;

            gap: 8px;

            padding: 10px;

            border-radius: 12px;
        }

        .ariya-combined-footer .af-feature:hover {
            transform: none;
        }

        .ariya-combined-footer .af-feature-icon {
            width: 37px;
            height: 37px;
            flex-basis: 37px;

            border-radius: 10px;
        }

        .ariya-combined-footer .af-feature-icon svg {
            width: 18px;
            height: 18px;
        }

        .ariya-combined-footer .af-feature h3 {
            font-size: 10.5px;
        }

        .ariya-combined-footer .af-feature p {
            font-size: 8.2px;
        }

        .ariya-combined-footer .af-panel {
            border-radius: 16px;
        }

        .ariya-combined-footer .af-brandbar {
            gap: 10px;
            padding: 14px 12px;
        }

        .ariya-combined-footer .af-logo {
            padding: 7px 10px;
        }

        .ariya-combined-footer .af-logo img {
            max-width: 96px;
            max-height: 38px;
        }

        .ariya-combined-footer .af-brandline {
            flex-basis: calc(100% - 120px);

            font-size: 9.5px;
            line-height: 1.8;
        }

        .ariya-combined-footer .af-socials {
            flex-basis: 100%;
        }

        .ariya-combined-footer .af-social {
            height: 32px;

            padding: 0 9px;

            font-size: 9px;
        }

        .ariya-combined-footer .af-main {
            display: grid;
            grid-template-columns: 1fr;

            gap: 13px;

            padding: 18px 12px;
        }

        .ariya-combined-footer .af-about {
            min-width: 0;
        }

        .ariya-combined-footer .af-title {
            font-size: 11.5px;
        }

        .ariya-combined-footer .af-about-text {
            font-size: 10px;
        }

        .ariya-combined-footer .af-links {
            display: flex;
            flex-direction: column;

            gap: 7px;

            min-width: 0;
        }

        .ariya-combined-footer .af-link-group {
            width: 100%;
            min-width: 0;

            border: 1px solid #dce9f1;
            border-radius: 10px;

            overflow: hidden;

            background: #f9fcfe;
        }

        .ariya-combined-footer .af-link-group > :not(summary) {
            display: none !important;
        }

        .ariya-combined-footer .af-link-group[open] > :not(summary) {
            display: block !important;
        }

        .ariya-combined-footer .af-summary {
            min-height: 43px;

            padding: 0 11px;

            cursor: pointer;
        }

        .ariya-combined-footer .af-summary .af-title {
            margin: 0;
            padding: 0;
        }

        .ariya-combined-footer .af-summary .af-title::after {
            display: none;
        }

        .ariya-combined-footer .af-summary svg {
            display: block;

            width: 15px;
            height: 15px;

            color: #4da3d4;

            transition: transform .18s ease;
        }

        .ariya-combined-footer details[open] .af-summary svg {
            transform: rotate(180deg);
        }

        .ariya-combined-footer .af-link-list,
        .ariya-combined-footer .af-empty {
            margin: 0;

            padding: 8px 12px 11px;

            border-top: 1px solid #e3edf3;
        }

        .ariya-combined-footer .af-license-wrap {
            width: 100%;
            min-width: 0;
        }

        .ariya-combined-footer .af-license {
            padding: 10px;

            border-radius: 11px;
        }

        .ariya-combined-footer .af-bottom {
            justify-content: center;

            padding: 11px 10px;

            text-align: center;

            font-size: 8.5px;
        }
    }

    @media (max-width: 430px) {

        .ariya-combined-footer .af-features {
            grid-template-columns: 1fr;
        }

        .ariya-combined-footer .af-feature {
            min-height: 67px;
        }

        .ariya-combined-footer .af-brandline {
            flex-basis: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .ariya-combined-footer *,
        .ariya-combined-footer *::before,
        .ariya-combined-footer *::after {
            transition: none !important;
            animation: none !important;
        }
    }

    /* ARIYA_FOOTER_MATCH_SITE_BG_V3 */

    /*
     * خود فوتر رنگ جداگانه ندارد؛
     * رنگ پس‌زمینه اصلی سایت از زیر آن دیده می‌شود.
     */
    .ariya-combined-footer {
        background: transparent !important;
        border-top: 0 !important;
    }

    /*
     * حذف هاله آبی بزرگ پس‌زمینه
     */
    .ariya-combined-footer::before {
        display: none !important;
        content: none !important;
        background: none !important;
    }

        /* ARIYA_FOOTER_ADDRESS_CLEAN_V1 */

        .ariya-combined-footer .af-address-clean {
            display: flex;
            align-items: flex-start;
            gap: 7px;

            margin-top: 14px;

            padding: 0 !important;

            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;

            font-size: 10px;
            line-height: 1.9;
        }

        .ariya-combined-footer .af-address-clean > i {
            flex: 0 0 auto;

            margin-top: 2px;

            color: #159fe3;

            font-size: 17px;
            line-height: 1.5;
        }

        .ariya-combined-footer .af-address-clean a,
        .ariya-combined-footer .af-address-clean span {
            display: inline;

            margin: 0 !important;
            padding: 0 !important;

            border: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .ariya-combined-footer .af-address-clean a {
            color: #0787d5;
            text-decoration: none !important;
        }

        .ariya-combined-footer .af-address-clean a:hover {
            color: #056eaf;
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
                        <div class="af-address-clean">
                            <i class="mdi mdi-map-marker-outline" aria-hidden="true"></i>

                            @if($addressLink)
                                <a href="{{ $addressLink }}" target="_blank" rel="noopener">
                                    {{ trim(strip_tags((string) $footerAddress)) }}
                                </a>
                            @else
                                <span>
                                    {{ trim(strip_tags((string) $footerAddress)) }}
                                </span>
                            @endif
                        </div>
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

<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#087ed1">
    <title>{{ option('site_coming_soon_title', 'آریا گستر؛ به‌زودی با تجربه‌ای تازه') }}</title>
    <style>
        :root {
            --primary: #087ed1;
            --primary-dark: #073b75;
            --primary-soft: #eaf6ff;
            --text: #172033;
            --muted: #64748b;
            --line: rgba(8, 126, 209, .16);
        }

        * { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            overflow-x: hidden;
            color: var(--text);
            background:
                radial-gradient(circle at 12% 12%, rgba(8,126,209,.12), transparent 34%),
                radial-gradient(circle at 88% 85%, rgba(7,59,117,.10), transparent 32%),
                linear-gradient(145deg, #f8fcff 0%, #ffffff 52%, #f5faff 100%);
            font-family: Tahoma, Arial, sans-serif;
            padding: 32px 18px;
        }

        .cs-shell {
            width: min(920px, 100%);
            position: relative;
        }

        .cs-decoration {
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            filter: blur(.2px);
        }

        .cs-decoration-one {
            width: 170px;
            height: 170px;
            top: -44px;
            left: -50px;
            background: linear-gradient(145deg, rgba(8,126,209,.12), rgba(8,126,209,.02));
        }

        .cs-decoration-two {
            width: 105px;
            height: 105px;
            right: -24px;
            bottom: -24px;
            border: 22px solid rgba(7,59,117,.07);
        }

        .cs-card {
            position: relative;
            z-index: 1;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 24px 70px rgba(28, 74, 114, .12);
            padding: clamp(34px, 6vw, 72px);
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .cs-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-dark), var(--primary), #35baf2);
        }

        .cs-logo-wrap {
            min-height: 88px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
        }

        .cs-logo {
            display: block;
            max-width: 150px;
            max-height: 92px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .cs-brand-fallback {
            width: 86px;
            height: 86px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 29px;
            font-weight: 700;
            border: 1px solid var(--line);
        }

        .cs-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 9px 15px;
            margin-bottom: 22px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-size: 13px;
            font-weight: 700;
        }

        .cs-badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
            box-shadow: 0 0 0 5px rgba(8,126,209,.12);
        }

        h1 {
            margin: 0;
            font-size: clamp(28px, 5vw, 48px);
            line-height: 1.45;
            letter-spacing: -.7px;
            color: var(--primary-dark);
        }

        .cs-description {
            max-width: 690px;
            margin: 22px auto 0;
            font-size: clamp(15px, 2.1vw, 18px);
            line-height: 2;
            color: var(--muted);
        }

        .cs-divider {
            width: 72px;
            height: 3px;
            margin: 30px auto;
            border-radius: 99px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
        }

        .cs-note {
            margin: 0;
            color: var(--text);
            font-size: 15px;
        }

        .cs-contact-area {
            margin-top: 26px;
        }

        .cs-phone {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: 11px 19px;
            color: var(--primary-dark);
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 13px;
            text-decoration: none;
            direction: ltr;
            font-size: 15px;
            font-weight: 700;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .cs-phone:hover,
        .cs-social-link:hover {
            transform: translateY(-2px);
            border-color: rgba(8,126,209,.38);
            box-shadow: 0 10px 28px rgba(8,126,209,.10);
        }

        .cs-phone svg {
            width: 20px;
            height: 20px;
            color: var(--primary);
        }

        .cs-socials {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            justify-content: center;
            gap: 11px;
            margin-top: 15px;
        }

        .cs-social-link {
            min-width: 112px;
            min-height: 66px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 10px 13px;
            color: var(--text);
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            text-decoration: none;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .cs-social-icon {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            color: #fff;
            overflow: hidden;
        }

        .cs-social-icon svg {
            width: 20px;
            height: 20px;
            display: block;
        }

        .cs-social-icon-instagram {
            background: linear-gradient(135deg, #7c3aed, #ec4899 56%, #f59e0b);
        }

        .cs-social-icon-whatsapp { background: #25d366; }
        .cs-social-icon-bale { background: #2d8cff; }
        .cs-social-icon-rubika { background: linear-gradient(135deg, #6d28d9, #ef3b85); }

        .cs-social-letter {
            font-size: 20px;
            font-weight: 800;
            line-height: 1;
        }

        .cs-social-copy {
            min-width: 0;
            text-align: right;
        }

        .cs-social-title {
            display: block;
            margin-bottom: 2px;
            color: var(--text);
            font-size: 13px;
            font-weight: 700;
        }

        .cs-social-id {
            display: block;
            max-width: 120px;
            overflow: hidden;
            color: var(--muted);
            font-size: 11px;
            white-space: nowrap;
            text-overflow: ellipsis;
            direction: ltr;
            text-align: left;
        }

        .cs-footer {
            margin-top: 28px;
            color: #94a3b8;
            font-size: 12px;
        }

        @media (max-width: 600px) {
            body { padding: 18px 12px; }
            .cs-card { border-radius: 18px; padding: 34px 20px; }
            .cs-logo-wrap { min-height: 72px; }
            .cs-logo { max-width: 124px; max-height: 76px; }
            .cs-description { line-height: 1.9; }
            .cs-socials { gap: 8px; }
            .cs-social-link { min-width: calc(50% - 4px); }
        }

        @media (prefers-reduced-motion: no-preference) {
            .cs-card { animation: cs-enter .55s ease both; }
            @keyframes cs-enter {
                from { opacity: 0; transform: translateY(14px); }
                to { opacity: 1; transform: translateY(0); }
            }
        }
    </style>
</head>
<body>
    @php
        $comingSoonPhone = option('site_coming_soon_phone', '90005202');
        $instagramId = trim((string) option('site_coming_soon_instagram', 'ariyajanebi.ir'));
        $whatsappNumber = trim((string) option('site_coming_soon_whatsapp', '09055019120'));
        $baleId = trim((string) option('site_coming_soon_bale', '09055019120'));
        $rubikaId = trim((string) option('site_coming_soon_rubika', '09055019120'));

        $normalizeIranMobile = static function ($value) {
            $digits = preg_replace('/\D+/', '', (string) $value);

            if (str_starts_with($digits, '0098')) {
                return substr($digits, 2);
            }

            if (str_starts_with($digits, '98')) {
                return $digits;
            }

            if (str_starts_with($digits, '0')) {
                return '98' . substr($digits, 1);
            }

            return $digits;
        };

        $profileUrl = static function ($value, $baseUrl) {
            $value = trim((string) $value);

            if ($value === '') {
                return null;
            }

            if (preg_match('/^https?:\/\//i', $value)) {
                return $value;
            }

            return rtrim($baseUrl, '/') . '/' . ltrim($value, '@/');
        };

        $instagramUrl = $profileUrl($instagramId, 'https://www.instagram.com');
        $whatsappUrl = $whatsappNumber !== '' ? 'https://wa.me/' . $normalizeIranMobile($whatsappNumber) : null;
        $baleUrl = $profileUrl($baleId, 'https://ble.ir');
        $rubikaUrl = $profileUrl($rubikaId, 'https://rubika.ir');
    @endphp

    <main class="cs-shell">
        <span class="cs-decoration cs-decoration-one"></span>
        <span class="cs-decoration cs-decoration-two"></span>

        <section class="cs-card" aria-labelledby="coming-soon-title">
            <div class="cs-logo-wrap">
                @if(option('info_logo'))
                    <img class="cs-logo" src="{{ asset(ltrim(option('info_logo'), '/')) }}" alt="{{ option('info_site_title', 'آریا گستر') }}">
                @else
                    <div class="cs-brand-fallback" aria-label="آریا گستر">آریا</div>
                @endif
            </div>

            <div class="cs-badge">
                <span class="cs-badge-dot"></span>
                در حال آماده‌سازی نسخه جدید
            </div>

            <h1 id="coming-soon-title">{{ option('site_coming_soon_title', 'آریا گستر؛ به‌زودی با تجربه‌ای تازه') }}</h1>

            <p class="cs-description">
                {{ option('site_coming_soon_description', 'در حال آماده‌سازی نسخه جدید وب‌سایت آریا گستر هستیم تا خرید عمده لوازم جانبی را سریع‌تر، ساده‌تر و حرفه‌ای‌تر کنیم. خیلی زود با محصولات و امکانات تازه در کنار شما خواهیم بود.') }}
            </p>

            <div class="cs-divider"></div>

            <p class="cs-note">{{ option('site_coming_soon_note', 'از همراهی و شکیبایی شما سپاسگزاریم.') }}</p>

            <div class="cs-contact-area" aria-label="راه‌های ارتباطی آریا گستر">
                @if($comingSoonPhone)
                    <a class="cs-phone" href="tel:{{ preg_replace('/[^0-9+]/', '', $comingSoonPhone) }}" aria-label="تماس تلفنی با آریا گستر">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.62a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.28-1.28a2 2 0 0 1 2.11-.45c.84.29 1.72.5 2.62.62A2 2 0 0 1 22 16.92z" />
                        </svg>
                        <span>{{ $comingSoonPhone }}</span>
                    </a>
                @endif

                <div class="cs-socials">
                    @if($instagramUrl)
                        <a class="cs-social-link" href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" aria-label="اینستاگرام آریا جانبی">
                            <span class="cs-social-icon cs-social-icon-instagram" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="5"></rect>
                                    <circle cx="12" cy="12" r="4"></circle>
                                    <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"></circle>
                                </svg>
                            </span>
                            <span class="cs-social-copy"><span class="cs-social-title">اینستاگرام</span><span class="cs-social-id">{{ '@' . ltrim($instagramId, '@') }}</span></span>
                        </a>
                    @endif

                    @if($whatsappUrl)
                        <a class="cs-social-link" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="گفت‌وگو در واتساپ">
                            <span class="cs-social-icon cs-social-icon-whatsapp" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.5 11.8a8.5 8.5 0 0 1-12.6 7.4L3 20.5l1.3-4.7A8.5 8.5 0 1 1 20.5 11.8z"></path>
                                    <path d="M8.4 7.7c.3-.5.6-.5.9-.5h.5c.2 0 .4.1.5.4l.8 1.8c.1.3.1.5-.1.7l-.6.8c-.2.2-.2.4 0 .7.5.9 1.3 1.7 2.2 2.2.3.2.5.2.7 0l.8-1c.2-.3.4-.3.7-.2l1.9.9c.3.1.4.3.4.5 0 .8-.4 1.6-1.1 2-.6.4-1.4.6-2.2.4-1.3-.3-3.1-1.1-4.8-2.8-1.4-1.4-2.3-3.1-2.6-4.4-.2-.7 0-1.3.4-1.9z"></path>
                                </svg>
                            </span>
                            <span class="cs-social-copy"><span class="cs-social-title">واتساپ</span><span class="cs-social-id">{{ $whatsappNumber }}</span></span>
                        </a>
                    @endif

                    @if($baleUrl)
                        <a class="cs-social-link" href="{{ $baleUrl }}" target="_blank" rel="noopener noreferrer" aria-label="گفت‌وگو در بله">
                            <span class="cs-social-icon cs-social-icon-bale" aria-hidden="true"><span class="cs-social-letter">ب</span></span>
                            <span class="cs-social-copy"><span class="cs-social-title">بله</span><span class="cs-social-id">{{ $baleId }}</span></span>
                        </a>
                    @endif

                    @if($rubikaUrl)
                        <a class="cs-social-link" href="{{ $rubikaUrl }}" target="_blank" rel="noopener noreferrer" aria-label="گفت‌وگو در روبیکا">
                            <span class="cs-social-icon cs-social-icon-rubika" aria-hidden="true"><span class="cs-social-letter">ر</span></span>
                            <span class="cs-social-copy"><span class="cs-social-title">روبیکا</span><span class="cs-social-id">{{ $rubikaId }}</span></span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="cs-footer">© {{ now()->year }} {{ option('info_site_title', 'آریا گستر') }}</div>
        </section>
    </main>
</body>
</html>

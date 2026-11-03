<!-- حساب کاربری هدر؛ لینک مدیریت عمداً در سایت عمومی نمایش داده نمی‌شود. -->
@php($ariyaVipBadge = auth()->check() ? \App\Services\VipCustomerBadgeService::forUser((int) auth()->id()) : null)
@once
<style>
.ariya-vip-header-badge { display:inline-flex; width:28px; height:28px; align-items:center; justify-content:center; vertical-align:middle; position:relative; margin-inline-start:6px; }
.ariya-vip-header-badge img { display:block; width:28px; height:28px; animation: ariyaVipMotion 2.8s ease-in-out infinite, ariyaVipGlow 2.2s ease-in-out infinite; }
.ariya-vip-header-emoji { font-size:23px; line-height:1; animation:ariyaVipMotion 2.8s ease-in-out infinite; }
@keyframes ariyaVipMotion { 0%,100% { transform:translateY(0) rotate(-4deg) scale(1); } 50% { transform:translateY(-2px) rotate(4deg) scale(1.09); } }
@keyframes ariyaVipGlow { 0%,100% { filter:drop-shadow(0 1px 2px rgba(49,104,255,.14)); } 50% { filter:drop-shadow(0 2px 7px rgba(173,93,246,.55)); } }
@media (prefers-reduced-motion:reduce) { .ariya-vip-header-badge img, .ariya-vip-header-emoji { animation:none!important; } }
</style>
@endonce

@if(auth()->check())

    @include('front::partials.language')

    <li class="nav-item account dropdown ariya-account-item">

        <a
            class="nav-link"
            href="#"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        >
            <span class="label-dropdown">
                {{ trans('front::messages.header.account') }}
            </span>

            @if($ariyaVipBadge)
                <span class="ariya-vip-header-badge" aria-label="مشتری VIP" title="مشتری VIP">
                    @if($ariyaVipBadge['type'] === 'emoji')
                        <span class="ariya-vip-header-emoji">{{ $ariyaVipBadge['emoji'] }}</span>
                    @else
                        <img src="{{ asset('assets/ariya/telegram-premium-star.svg') }}" width="28" height="28" alt="ستاره VIP">
                    @endif
                </span>
            @else
                <i class="mdi mdi-account-circle-outline"></i>
            @endif
        </a>

        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-left">

            <a
                class="dropdown-item"
                href="{{ route('front.user.profile') }}"
            >
                <i class="mdi mdi-account-card-details-outline"></i>
                {{ trans('front::messages.header.profile') }}
            </a>

            <a
                class="dropdown-item"
                href="{{ route('front.orders.index') }}"
            >
                <i class="mdi mdi-account-edit-outline"></i>
                {{ trans('front::messages.header.my-orders') }}
            </a>

            <div
                class="dropdown-divider"
                role="presentation"
            ></div>

            <a
                class="dropdown-item"
                href="{{ route('logout') }}"
            >
                <i class="mdi mdi-logout-variant"></i>
                {{ trans('front::messages.header.exit') }}
            </a>

        </div>

    </li>

@else

    @include('front::partials.language')

    <li class="nav-item account ariya-account-item">

        <a
            class="nav-link"
            href="{{ route('login') }}"
        >
            <span class="label-dropdown">
                ورود / عضویت
            </span>

            <i class="mdi mdi-account-circle-outline"></i>
        </a>

    </li>

@endif

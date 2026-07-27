<!-- حساب کاربری هدر؛ لینک مدیریت عمداً در سایت عمومی نمایش داده نمی‌شود. -->
@if(auth()->check())
    @include('front::partials.language')

    <li class="nav-item account dropdown ariya-account-item">
        <a class="nav-link" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="label-dropdown">{{ trans('front::messages.header.account') }}</span>
            <i class="mdi mdi-account-circle-outline"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-left">
            <a class="dropdown-item" href="{{ route('front.user.profile') }}">
                <i class="mdi mdi-account-card-details-outline"></i>{{ trans('front::messages.header.profile') }}
            </a>
            <a class="dropdown-item" href="{{ route('front.orders.index') }}">
                <i class="mdi mdi-account-edit-outline"></i>{{ trans('front::messages.header.my-orders') }}
            </a>
            <div class="dropdown-divider" role="presentation"></div>
            <a class="dropdown-item" href="{{ route('logout') }}">
                <i class="mdi mdi-logout-variant"></i>{{ trans('front::messages.header.exit') }}
            </a>
        </div>
    </li>
@else
    @include('front::partials.language')

    <li class="nav-item account dropdown ariya-account-item">
        <a class="nav-link" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="label-dropdown">{{ trans('front::messages.header.account') }}</span>
            <i class="mdi mdi-account-circle-outline"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-left">
            <a class="dropdown-item" href="{{ route('login') }}">
                <i class="mdi mdi-account-card-details-outline"></i>
                {{ trans('front::messages.header.sign-in-to-site') }}
            </a>
            <a class="dropdown-item" href="{{ route('register') }}">
                <i class="mdi mdi-account-edit-outline"></i>
                {{ trans('front::messages.header.register') }}
            </a>
        </div>
    </li>
@endif

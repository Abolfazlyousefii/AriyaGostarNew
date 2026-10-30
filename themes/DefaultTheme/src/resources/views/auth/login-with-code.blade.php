@extends('front::auth.layouts.master', ['title' => 'ورود / ثبت‌نام'])

@section('content')

<main class="main-content dt-sl mt-0 mb-0 ariya-login-page" dir="rtl">
    <div class="container main-container ariya-login-container">

        <div class="row ariya-login-layout">
            <div class="col-12 ariya-login-form-column">

                <div class="form-ui dt-sl dt-sn pt-4 ariya-login-panel">

                    <div class="ariya-login-eyebrow">
                        <span class="ariya-login-pulse"></span>
                        حساب کاربری آریا جانبی
                    </div>

                    <div class="section-title title-wide mb-1 no-after-title-wide">
                        <h2 class="font-weight-bold">
                            ورود / ثبت‌نام
                        </h2>
                    </div>

                    <div class="message-light mb-3">
                        شماره موبایل خود را وارد کنید.
                        اگر قبلاً عضو شده باشید، کد ورود برای شما ارسال می‌شود.
                        در غیر این صورت به فرم درخواست عضویت منتقل خواهید شد.
                    </div>

                    @if(session('status'))
                        <div class="alert alert-warning mb-3">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger mb-3">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form
                        id="login-with-code-form"
                        action="{{ route('login-with-code.send') }}"
                        method="POST"
                    >
                        @csrf

                        <div class="form-row-title">
                            <h3>شماره موبایل</h3>
                        </div>

                        <div class="form-row with-icon form-group">

                            <input
                                id="mobile"
                                type="tel"
                                inputmode="numeric"
                                autocomplete="tel"
                                name="mobile"
                                class="input-ui pr-2"
                                placeholder="مثلاً 09123456789"
                                maxlength="14"
                                value="{{ old('mobile', request('mobile')) }}"
                                required
                            >

                            <i class="mdi mdi-cellphone"></i>

                        </div>

                        <div class="form-row mt-3">
                            <button
                                type="submit"
                                class="btn-primary-cm btn-with-icon mx-auto w-100"
                            >
                                <i class="mdi mdi-login-variant"></i>
                                ادامه
                            </button>
                        </div>

                    </form>

                    {{-- ARIYA_LOGIN_INLINE_OTP_V1 --}}
                    <section id="ariya-otp-stage" class="ariya-otp-stage" hidden aria-label="تأیید شماره موبایل">

                        <div class="ariya-otp-intro">
                            <div class="ariya-otp-intro-icon">
                                <i class="mdi mdi-message-text-outline" aria-hidden="true"></i>
                            </div>
                            <div>
                                <strong>کد تأیید ارسال شد</strong>
                                <p>
                                    کد پیامک‌شده به شماره
                                    <b id="ariya-otp-mobile-display" dir="ltr"></b>
                                    را وارد کنید.
                                </p>
                            </div>
                        </div>

                        <button type="button" class="ariya-otp-edit" id="ariya-otp-edit">
                            <i class="mdi mdi-pencil-outline" aria-hidden="true"></i>
                            ویرایش شماره موبایل
                        </button>

                        <div class="ariya-otp-error" id="ariya-otp-error" role="alert" hidden></div>

                        <form id="ariya-otp-form" action="{{ route('login-with-code.confirm') }}" method="POST">
                            @csrf
                            <input type="hidden" name="mobile" id="ariya-otp-mobile">

                            <label for="ariya-otp-code" class="ariya-otp-label">
                                کد تأیید
                            </label>

                            <input
                                id="ariya-otp-code"
                                type="text"
                                name="verify_code"
                                class="ariya-otp-input"
                                inputmode="numeric"
                                autocomplete="one-time-code"
                                pattern="[0-9]*"
                                maxlength="10"
                                placeholder="کد پیامک‌شده"
                                aria-describedby="ariya-otp-note"
                                required
                            >

                            <small id="ariya-otp-note" class="ariya-otp-note">
                                کد دریافتی را بدون فاصله وارد کنید.
                            </small>

                            <button type="submit" class="ariya-otp-confirm">
                                <i class="mdi mdi-check-circle-outline" aria-hidden="true"></i>
                                تأیید و ورود به حساب
                            </button>
                        </form>

                        <div class="ariya-otp-resend-row">
                            <span>پیامک را دریافت نکردید؟</span>
                            <button type="button" id="ariya-otp-resend" disabled>
                                ارسال مجدد (۰۲:۰۰)
                            </button>
                        </div>

                    </section>

                </div>
            </div>
            <aside class="col-12 ariya-login-story-column" aria-label="آشنایی با حساب کاربری">
                <div class="ariya-login-story">

                    <div class="ariya-login-story-top">
                        <span class="ariya-login-story-logo">
                            <i class="mdi mdi-shield-check-outline" aria-hidden="true"></i>
                            تجربه خرید راحت‌تر
                        </span>
                        <span class="ariya-login-story-small">آریا جانبی</span>
                    </div>

                    <div class="ariya-login-visual" aria-hidden="true">
                        <div class="ariya-login-visual-ring ariya-login-visual-ring-one"></div>
                        <div class="ariya-login-visual-ring ariya-login-visual-ring-two"></div>

                        <div class="ariya-login-visual-card">
                            <div class="ariya-login-visual-icon">
                                <svg viewBox="0 0 64 64" fill="none" aria-hidden="true">
                                    <rect x="19" y="7" width="26" height="50" rx="7" stroke="currentColor" stroke-width="3"/>
                                    <path d="M28 13H36" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    <path d="M30 50H34" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                    <circle cx="32" cy="31" r="11" stroke="currentColor" stroke-width="2.5"/>
                                    <path d="M27 31L30.5 34.5L37 27.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span class="ariya-login-visual-check">
                                <i class="mdi mdi-check" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div class="ariya-login-float ariya-login-float-one">
                            <i class="mdi mdi-package-variant-closed" aria-hidden="true"></i>
                        </div>

                        <div class="ariya-login-float ariya-login-float-two">
                            <i class="mdi mdi-cart-outline" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="ariya-login-story-content">
                        <span class="ariya-login-story-kicker">خوش اومدی به آریا جانبی</span>

                        <h1>
                            تجربه یک خرید
                            <span>ساده، سریع و مطمئن</span>
                        </h1>

                        <p>
                            با شماره موبایلت وارد شو و سفارش‌ها، سبد خرید
                            و اطلاعات حساب کاربری‌ات رو راحت‌تر مدیریت کن.
                        </p>

                        <div class="ariya-login-story-benefits">
                            <span>
                                <i class="mdi mdi-check-circle-outline" aria-hidden="true"></i>
                                ورود با کد یک‌بارمصرف
                            </span>

                            <span>
                                <i class="mdi mdi-check-circle-outline" aria-hidden="true"></i>
                                پیگیری سفارش‌ها
                            </span>

                            <span>
                                <i class="mdi mdi-check-circle-outline" aria-hidden="true"></i>
                                خرید راحت‌تر
                            </span>
                        </div>
                    </div>

                    <div class="ariya-login-story-bottom">
                        <span>ARYA JANEBI</span>
                        <span>خوشحالیم که کنار مایی</span>
                    </div>

                </div>
            </aside>
        </div>

    </div>
</main>

@endsection

{{-- ARIYA_LOGIN_BLUE_PREMIUM_V1 --}}
@push('styles')
<style>
/* Login design - isolated from other pages */

.ariya-login-page,
.ariya-login-page * {
    box-sizing: border-box;
}

.ariya-login-page {
    position: relative;
    isolation: isolate;
    margin: 0 !important;
    padding: 42px 18px 58px;
    min-height: calc(100vh - 250px);
    overflow: hidden;
    background:
        radial-gradient(ellipse at 95% 5%, rgba(20,154,223,.13), transparent 38%),
        radial-gradient(ellipse at 0% 95%, rgba(17,87,183,.08), transparent 42%),
        #f5f8fc;
}

.ariya-login-page .ariya-login-container {
    width: 100%;
    max-width: 1140px !important;
    padding: 0 !important;
    margin: 0 auto !important;
}

.ariya-login-page .ariya-login-layout {
    display: grid !important;
    grid-template-columns: minmax(0, .88fr) minmax(0, 1.12fr);
    gap: 24px;
    direction: rtl;
    align-items: stretch;
    margin: 0 !important;
}

.ariya-login-page .ariya-login-form-column,
.ariya-login-page .ariya-login-story-column {
    min-width: 0;
    width: 100%;
    max-width: none !important;
    flex: none !important;
    margin: 0 !important;
    padding: 0 !important;
}

.ariya-login-page .ariya-login-panel {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 535px;
    height: 100%;
    padding: 42px 37px !important;
    background: #fff !important;
    border: 1px solid #e4edf5 !important;
    border-radius: 25px !important;
    box-shadow:
        0 20px 55px rgba(18,53,97,.065),
        0 3px 10px rgba(18,53,97,.025) !important;
    overflow: visible !important;
}

.ariya-login-page .ariya-login-eyebrow {
    display: inline-flex;
    align-items: center;
    align-self: flex-start;
    gap: 9px;
    padding: 8px 13px;
    margin-bottom: 23px;
    border: 1px solid #d9ecfb;
    border-radius: 50px;
    background: #f1f9ff;
    color: #087cbd;
    font-size: 12px;
    font-weight: 700;
}

.ariya-login-page .ariya-login-pulse {
    width: 8px;
    height: 8px;
    background: #159fe3;
    border-radius: 50%;
    box-shadow: 0 0 0 4px rgba(21,159,227,.12);
}

.ariya-login-page .section-title.title-wide {
    margin: 0 0 18px !important;
    padding: 0 !important;
    border: none !important;
}

.ariya-login-page .section-title.title-wide::after,
.ariya-login-page .section-title.title-wide::before {
    display: none !important;
}

.ariya-login-page .section-title h2 {
    margin: 0 !important;
    color: #142f4c !important;
    font-size: clamp(24px, 2.5vw, 29px) !important;
    line-height: 1.5 !important;
    font-weight: 800 !important;
    text-align: right !important;
}

.ariya-login-page .message-light {
    display: block !important;
    padding: 15px 17px !important;
    margin: 0 0 30px !important;
    background: #f2f8ff !important;
    color: #516b83 !important;
    border: 1px solid #deebfa !important;
    border-radius: 15px !important;
    box-shadow: none !important;
    font-size: 13px !important;
    line-height: 2.05 !important;
    text-align: right !important;
}

.ariya-login-page .alert {
    border-radius: 13px !important;
    line-height: 1.9;
    font-size: 13px;
}

.ariya-login-page #login-with-code-form {
    width: 100%;
}

.ariya-login-page .form-row-title {
    margin-bottom: 10px !important;
}

.ariya-login-page .form-row-title h3 {
    margin: 0 !important;
    color: #253f59 !important;
    font-size: 14px !important;
    font-weight: 750 !important;
    text-align: right !important;
}

.ariya-login-page .form-row.with-icon {
    display: block !important;
    position: relative !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 0 13px !important;
}

.ariya-login-page .form-row.with-icon input.input-ui {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    height: 56px !important;
    min-height: 56px !important;
    padding: 0 49px 0 17px !important;
    margin: 0 !important;
    color: #193956 !important;
    background: #fbfdff !important;
    border: 1px solid #dbe6f0 !important;
    border-radius: 14px !important;
    box-shadow: none !important;
    font-size: 15px !important;
    direction: ltr !important;
    text-align: left !important;
    outline: none !important;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.ariya-login-page .form-row.with-icon input.input-ui::placeholder {
    color: #9aabb9 !important;
    font-size: 13px !important;
    opacity: 1;
}

.ariya-login-page .form-row.with-icon input.input-ui:focus {
    background: #fff !important;
    border-color: #168bd0 !important;
    box-shadow: 0 0 0 4px rgba(21,159,227,.11) !important;
}

.ariya-login-page .form-row.with-icon > i.mdi {
    position: absolute !important;
    top: 50% !important;
    right: 16px !important;
    left: auto !important;
    transform: translateY(-50%) !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    width: 23px !important;
    height: 23px !important;
    margin: 0 !important;
    padding: 0 !important;
    color: #3997cf !important;
    font-size: 22px !important;
    line-height: 1 !important;
    pointer-events: none;
    z-index: 1;
}

.ariya-login-page .form-row.mt-3 {
    display: block !important;
    width: 100% !important;
    margin: 19px 0 0 !important;
    padding: 0 !important;
}

.ariya-login-page #login-with-code-form button.btn-primary-cm {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 11px;
    position: relative !important;
    width: 100% !important;
    height: 54px !important;
    min-height: 54px !important;
    padding: 0 20px !important;
    margin: 0 !important;
    border: none !important;
    border-radius: 14px !important;
    background: linear-gradient(115deg, #0b68bc, #159fe3) !important;
    color: #fff !important;
    box-shadow: 0 10px 23px rgba(11,114,196,.22) !important;
    font-size: 15px !important;
    font-weight: 800 !important;
    line-height: 1.5 !important;
    text-align: center !important;
    cursor: pointer;
    transition: transform .18s ease, box-shadow .18s ease;
}

.ariya-login-page #login-with-code-form button.btn-primary-cm > i {
    position: static !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    width: auto !important;
    height: auto !important;
    padding: 0 !important;
    margin: 0 !important;
    background: none !important;
    color: #fff !important;
    font-size: 21px !important;
    line-height: 1 !important;
    transform: none !important;
}

.ariya-login-page #login-with-code-form button.btn-primary-cm:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 15px 29px rgba(11,114,196,.28) !important;
}

.ariya-login-page #login-with-code-form button.btn-primary-cm:focus-visible {
    outline: 3px solid #a3dafc !important;
    outline-offset: 3px;
}

.ariya-login-page .ariya-login-story {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    min-height: 535px;
    height: 100%;
    padding: 28px 33px;
    border-radius: 25px;
    overflow: hidden;
    isolation: isolate;
    background:
        radial-gradient(circle at 12% 16%, rgba(111,218,255,.34), transparent 37%),
        radial-gradient(circle at 90% 90%, rgba(11,42,139,.48), transparent 45%),
        linear-gradient(135deg, #073b86 0%, #086fbc 54%, #159fe3 100%);
    box-shadow: 0 20px 48px rgba(15,87,157,.16);
    color: #fff;
}

.ariya-login-page .ariya-login-story::before {
    content: "";
    position: absolute;
    z-index: -1;
    width: 390px;
    height: 390px;
    top: -190px;
    left: -140px;
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 50%;
    box-shadow:
        0 0 0 60px rgba(255,255,255,.035),
        0 0 0 125px rgba(255,255,255,.025);
    pointer-events: none;
}

.ariya-login-page .ariya-login-story-top,
.ariya-login-page .ariya-login-story-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    position: relative;
    z-index: 2;
}

.ariya-login-page .ariya-login-story-logo {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 750;
    color: #fff;
}

.ariya-login-page .ariya-login-story-logo i {
    font-size: 21px;
}

.ariya-login-page .ariya-login-story-small,
.ariya-login-page .ariya-login-story-bottom {
    color: rgba(255,255,255,.67);
    font-size: 11px;
}

.ariya-login-page .ariya-login-visual {
    position: relative;
    width: 210px;
    height: 150px;
    margin: 10px auto 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ariya-login-page .ariya-login-visual-ring {
    position: absolute;
    inset: 8px 40px;
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 50%;
    transform: scale(1.6);
}

.ariya-login-page .ariya-login-visual-ring-two {
    transform: scale(1.05);
    border-style: dashed;
    opacity: .75;
}

.ariya-login-page .ariya-login-visual-card {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 116px;
    height: 116px;
    border: 1px solid rgba(255,255,255,.35);
    border-radius: 27px;
    background: rgba(255,255,255,.16);
    box-shadow: 0 14px 34px rgba(0,33,95,.12);
    backdrop-filter: blur(8px);
    transform: rotate(-7deg);
}

.ariya-login-page .ariya-login-visual-icon {
    color: #fff;
}

.ariya-login-page .ariya-login-visual-icon svg {
    width: 72px;
    height: 72px;
}

.ariya-login-page .ariya-login-visual-check {
    position: absolute;
    bottom: -9px;
    right: -9px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 37px;
    height: 37px;
    border: 4px solid #168ed0;
    border-radius: 50%;
    background: #fff;
    color: #0c83c5;
    font-size: 19px;
}

.ariya-login-page .ariya-login-float {
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    width: 41px;
    height: 41px;
    border: 1px solid rgba(255,255,255,.26);
    border-radius: 14px;
    background: rgba(255,255,255,.19);
    color: #fff;
    font-size: 23px;
}

.ariya-login-page .ariya-login-float-one {
    top: 3px;
    right: 7px;
    transform: rotate(12deg);
}

.ariya-login-page .ariya-login-float-two {
    bottom: 6px;
    left: 5px;
    transform: rotate(-12deg);
}

.ariya-login-page .ariya-login-story-content {
    position: relative;
    z-index: 2;
}

.ariya-login-page .ariya-login-story-kicker {
    display: block;
    margin-bottom: 10px;
    color: #bceaff;
    font-size: 13px;
    font-weight: 650;
}

.ariya-login-page .ariya-login-story h1 {
    margin: 0 0 13px;
    color: #fff !important;
    font-size: clamp(26px, 2.8vw, 35px);
    line-height: 1.58;
    font-weight: 850;
}

.ariya-login-page .ariya-login-story h1 span {
    display: block;
    color: #a7e9ff;
}

.ariya-login-page .ariya-login-story-content p {
    margin: 0 0 22px;
    max-width: 440px;
    color: rgba(255,255,255,.78);
    font-size: 13px;
    line-height: 2.05;
}

.ariya-login-page .ariya-login-story-benefits {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ariya-login-page .ariya-login-story-benefits span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 8px;
    background: rgba(255,255,255,.09);
    color: #fff;
    font-size: 11px;
    line-height: 1.6;
}

.ariya-login-page .ariya-login-story-benefits i {
    font-size: 15px;
}

.ariya-login-page .ariya-login-story-bottom {
    padding-top: 18px;
    margin-top: 23px;
    border-top: 1px solid rgba(255,255,255,.13);
}

@media (max-width: 991.98px) {
    .ariya-login-page {
        padding: 28px 15px 45px;
    }

    .ariya-login-page .ariya-login-layout {
        display: block !important;
        max-width: 490px;
        margin: 0 auto !important;
    }

    .ariya-login-page .ariya-login-story-column {
        display: none !important;
    }

    .ariya-login-page .ariya-login-panel {
        min-height: auto;
        padding: 32px 28px !important;
    }
}

@media (max-width: 575.98px) {
    .ariya-login-page {
        padding: 20px 12px 35px;
        min-height: auto;
    }

    .ariya-login-page .ariya-login-panel {
        padding: 29px 21px !important;
        border-radius: 19px !important;
    }

    .ariya-login-page .ariya-login-eyebrow {
        margin-bottom: 19px;
    }

    .ariya-login-page .section-title h2 {
        font-size: 24px !important;
    }

    .ariya-login-page .message-light {
        font-size: 12px !important;
        margin-bottom: 25px !important;
    }

    .ariya-login-page .form-row.with-icon input.input-ui {
        height: 53px !important;
        min-height: 53px !important;
    }

    .ariya-login-page #login-with-code-form button.btn-primary-cm {
        min-height: 53px !important;
        height: 53px !important;
    }
}

@media (prefers-reduced-motion: reduce) {
    .ariya-login-page *,
    .ariya-login-page *::before,
    .ariya-login-page *::after {
        transition: none !important;
        animation: none !important;
    }
}
</style>
@endpush

@push('styles')
<style>
/* ARIYA_LOGIN_INLINE_OTP_V1 */

.ariya-login-page.ariya-otp-active #login-with-code-form,
.ariya-login-page.ariya-otp-active .ariya-login-panel > .message-light {
    display: none !important;
}

.ariya-login-page .ariya-otp-stage[hidden],
.ariya-login-page .ariya-otp-error[hidden] {
    display: none !important;
}

.ariya-login-page .ariya-otp-stage {
    width: 100%;
    margin-top: 14px;
}

.ariya-login-page .ariya-otp-intro {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 17px;
    background: #f1f9ff;
    border: 1px solid #dcecf8;
    border-radius: 15px;
}

.ariya-login-page .ariya-otp-intro-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 43px;
    height: 43px;
    border-radius: 13px;
    background: #dbf0fd;
    color: #0787cf;
    font-size: 23px;
}

.ariya-login-page .ariya-otp-intro strong {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
    color: #173b59;
}

.ariya-login-page .ariya-otp-intro p {
    margin: 0;
    color: #54718c;
    font-size: 12px;
    line-height: 2;
}

.ariya-login-page .ariya-otp-intro b {
    display: inline-block;
    color: #087bbd;
}

.ariya-login-page .ariya-otp-edit {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 12px 0 21px;
    padding: 4px 0;
    border: none;
    background: none;
    color: #087fc4;
    font-size: 12px;
    cursor: pointer;
}

.ariya-login-page .ariya-otp-label {
    display: block;
    margin: 0 0 10px;
    color: #253f59;
    font-size: 14px;
    font-weight: 700;
}

.ariya-login-page .ariya-otp-input {
    display: block !important;
    width: 100% !important;
    height: 58px !important;
    padding: 0 15px !important;
    border: 1px solid #dbe6f0 !important;
    border-radius: 14px !important;
    outline: none !important;
    background: #fbfdff !important;
    color: #143d65 !important;
    font-family: inherit;
    font-size: 23px !important;
    font-weight: 700;
    letter-spacing: .22em;
    direction: ltr;
    text-align: center;
    box-shadow: none !important;
}

.ariya-login-page .ariya-otp-input:focus {
    border-color: #159fe3 !important;
    background: #fff !important;
    box-shadow: 0 0 0 4px rgba(21,159,227,.12) !important;
}

.ariya-login-page .ariya-otp-input::placeholder {
    font-size: 13px;
    font-weight: 400;
    letter-spacing: normal;
    color: #9aacbc;
}

.ariya-login-page .ariya-otp-note {
    display: block;
    margin-top: 9px;
    color: #8295a8;
    font-size: 11px;
}

.ariya-login-page .ariya-otp-confirm {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px;
    width: 100% !important;
    height: 54px !important;
    margin-top: 22px !important;
    padding: 0 15px !important;
    border: none !important;
    border-radius: 14px !important;
    background: linear-gradient(115deg, #0b68bc, #159fe3) !important;
    color: #fff !important;
    font-size: 15px !important;
    font-weight: 800;
    box-shadow: 0 10px 23px rgba(11,114,196,.2) !important;
    cursor: pointer;
}

.ariya-login-page .ariya-otp-confirm:disabled {
    opacity: .7;
    cursor: wait;
}

.ariya-login-page .ariya-otp-resend-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 22px;
    padding-top: 17px;
    border-top: 1px solid #e8eef5;
    color: #8192a3;
    font-size: 12px;
}

.ariya-login-page .ariya-otp-resend-row button {
    padding: 4px;
    border: 0;
    background: transparent;
    color: #087fc4;
    font-size: 12px;
    cursor: pointer;
}

.ariya-login-page .ariya-otp-resend-row button:disabled {
    color: #8296aa;
    cursor: default;
}

.ariya-login-page .ariya-otp-error {
    margin-bottom: 15px;
    padding: 12px 14px;
    border: 1px solid #f3cece;
    border-radius: 12px;
    background: #fff5f5;
    color: #a23131;
    font-size: 12px;
    line-height: 1.9;
}

.ariya-login-page .ariya-otp-edit:focus-visible,
.ariya-login-page .ariya-otp-resend-row button:focus-visible,
.ariya-login-page .ariya-otp-confirm:focus-visible {
    outline: 3px solid #92d6ff !important;
    outline-offset: 3px;
}

@media (max-width: 575px) {
    .ariya-login-page .ariya-otp-intro {
        padding: 13px;
    }
    .ariya-login-page .ariya-otp-input {
        height: 54px !important;
    }
}
</style>
@endpush
@push('scripts')
<script>
(function ($) {
    'use strict';

    $(function () {
        var $page = $('.ariya-login-page');
        var $sendForm = $('#login-with-code-form');
        var $otpStage = $('#ariya-otp-stage');
        var $otpForm = $('#ariya-otp-form');
        var $otpCode = $('#ariya-otp-code');
        var $otpMobile = $('#ariya-otp-mobile');
        var $otpDisplay = $('#ariya-otp-mobile-display');
        var $otpError = $('#ariya-otp-error');
        var $resend = $('#ariya-otp-resend');
        var $edit = $('#ariya-otp-edit');
        var $title = $page.find('.section-title h2').first();

        var originalTitle = $title.text();
        var currentMobile = '';
        var timer = null;
        var resendDeadline = 0;
        var busy = false;

        if (!$sendForm.length || !$otpForm.length) return;

        function latinDigits(value) {
            return String(value || '')
                .replace(/[۰-۹]/g, function (c) {
                    return '۰۱۲۳۴۵۶۷۸۹'.indexOf(c);
                })
                .replace(/[٠-٩]/g, function (c) {
                    return '٠١٢٣٤٥٦٧٨٩'.indexOf(c);
                });
        }

        function firstError(xhr) {
            var payload = xhr.responseJSON || {};
            var errors = payload.errors || {};

            for (var key in errors) {
                if (Object.prototype.hasOwnProperty.call(errors, key)) {
                    var value = errors[key];
                    if (Array.isArray(value) && value.length) return value[0];
                    if (typeof value === 'string') return value;
                }
            }

            return payload.message || 'در انجام درخواست مشکلی پیش آمد. دوباره تلاش کنید.';
        }

        function showError(message) {
            $otpError.text(message).prop('hidden', false);
        }

        function clearError() {
            $otpError.text('').prop('hidden', true);
        }

        function navigate(url) {
            try {
                var target = new URL(url, window.location.href);
                if (target.origin !== window.location.origin) {
                    throw new Error('Unexpected redirect origin');
                }
                window.location.assign(target.href);
            } catch (e) {
                showError('آدرس انتقال معتبر نیست. لطفاً صفحه را تازه‌سازی کنید.');
            }
        }

        function stopTimer() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        function updateCountdown() {
            var remaining = Math.max(0, Math.ceil((resendDeadline - Date.now()) / 1000));

            if (!remaining) {
                stopTimer();
                $resend.prop('disabled', false).text('ارسال مجدد کد');
                return;
            }

            var mins = String(Math.floor(remaining / 60)).padStart(2, '0');
            var secs = String(remaining % 60).padStart(2, '0');

            $resend.prop('disabled', true)
                .text('ارسال مجدد (' + mins + ':' + secs + ')');
        }

        function startTimer(data) {
            var seconds = Number(data && (data.resend_after || data.resend_seconds)) || 120;
            var deadline = Number(data && data.resend_time);

            if (deadline > 1000000000) {
                seconds = Math.ceil(deadline - Date.now() / 1000);
            }

            seconds = Math.max(0, Math.min(600, seconds));
            resendDeadline = Date.now() + seconds * 1000;

            stopTimer();
            updateCountdown();

            if (seconds > 0) {
                timer = setInterval(updateCountdown, 1000);
            }
        }

        function openOtp(mobile, data) {
            currentMobile = mobile;
            $otpMobile.val(mobile);
            $otpDisplay.text(mobile);
            $otpCode.val('');
            clearError();

            $page.addClass('ariya-otp-active');
            $otpStage.prop('hidden', false);
            $title.text('تأیید شماره موبایل');

            startTimer(data);
            $otpCode.trigger('focus');
        }

        function handleSendResponse(data, mobile) {
            if (data && data.status === 'otp_sent') {
                openOtp(mobile, data);
                return;
            }

            if (data && data.redirect) {
                navigate(data.redirect);
                return;
            }

            showError((data && data.message) || 'پاسخ ارسال کد قابل تشخیص نیست.');
        }

        function requestCode(isResend) {
            if (busy) return;

            var $mobileInput = $sendForm.find('[name="mobile"]');
            var mobile = latinDigits($mobileInput.val()).replace(/\s+/g, '').trim();

            $mobileInput.val(mobile);
            currentMobile = mobile;

            clearError();
            busy = true;
            $sendForm.find('button[type="submit"]').prop('disabled', true);
            $resend.prop('disabled', true);

            $.ajax({
                url: $sendForm.attr('action'),
                method: 'POST',
                data: $sendForm.serialize(),
                dataType: 'json'
            }).done(function (data) {
                handleSendResponse(data, mobile);
            }).fail(function (xhr) {
                var message = firstError(xhr);

                if (isResend || !$otpStage.prop('hidden')) {
                    showError(message);
                } else if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    window.alert(message);
                }

                if (isResend) {
                    updateCountdown();
                }
            }).always(function () {
                busy = false;
                $sendForm.find('button[type="submit"]').prop('disabled', false);
                if (!$otpStage.prop('hidden')) updateCountdown();
            });
        }

        $sendForm.off('submit.ariyaInlineOtp').on('submit.ariyaInlineOtp', function (event) {
            event.preventDefault();
            requestCode(false);
        });

        $otpForm.on('submit', function (event) {
            event.preventDefault();
            if (busy) return;

            var code = latinDigits($otpCode.val()).replace(/\D/g, '');
            $otpCode.val(code);

            if (!code) {
                showError('کد تأیید را وارد کنید.');
                $otpCode.trigger('focus');
                return;
            }

            busy = true;
            clearError();
            var $button = $otpForm.find('button[type="submit"]');
            $button.prop('disabled', true);

            $.ajax({
                url: $otpForm.attr('action'),
                method: 'POST',
                data: $otpForm.serialize()
            }).done(function (data) {
                var success =
                    data === 'success' ||
                    (data && (
                        data.status === 'success' ||
                        data.status === 'verified' ||
                        data.status === 'authenticated' ||
                        data.authenticated === true ||
                        data.redirect
                    ));

                if (success) {
                    navigate((data && data.redirect) || @json(route('front.index')));
                    return;
                }

                showError('پاسخ تأیید کد قابل تشخیص نیست. لطفاً دوباره تلاش کنید.');
            }).fail(function (xhr) {
                showError(firstError(xhr));
                $otpCode.trigger('focus').trigger('select');
            }).always(function () {
                busy = false;
                $button.prop('disabled', false);
            });
        });

        $edit.on('click', function () {
            if (busy) return;

            stopTimer();
            $page.removeClass('ariya-otp-active');
            $otpStage.prop('hidden', true);
            $title.text(originalTitle);
            $otpCode.val('');
            clearError();
            $sendForm.find('[name="mobile"]').trigger('focus');
        });

        $resend.on('click', function () {
            if (busy || $resend.prop('disabled')) return;
            requestCode(true);
        });

        $otpCode.on('input', function () {
            this.value = latinDigits(this.value).replace(/\D/g, '');
            clearError();
        });
    });
})(jQuery);
</script>
@endpush

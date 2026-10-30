@extends('front::layouts.master', ['title' => 'خرید سریع عمده'])

@push('styles')
<style>
/* ARIYA_TASK01_STAGE04_VIEW_V2 */
.ariya-qw-page {
    direction: rtl;
    padding: 28px 0 65px;
    min-height: 55vh;
}
.ariya-qw-wrap {
    max-width: 900px;
    margin: 0 auto;
}
.ariya-qw-breadcrumb {
    margin-bottom: 20px;
    font-size: 13px;
    color: #64748b;
}
.ariya-qw-card {
    padding: 30px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 12px 35px rgba(15,23,42,.05);
}
.ariya-qw-card h1 {
    margin: 0 0 13px;
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
}
.ariya-qw-description {
    color: #64748b;
    line-height: 2;
    margin-bottom: 25px;
}
.ariya-qw-form label {
    display: block;
    margin-bottom: 10px;
    font-weight: 700;
    color: #334155;
}
.ariya-qw-form select {
    display: block;
    width: 100%;
    min-height: 50px;
    padding: 9px 15px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    font: inherit;
    color: #0f172a;
}
.ariya-qw-form button {
    display: inline-block;
    margin-top: 16px;
    padding: 12px 24px;
    min-height: 46px;
    border: 0;
    border-radius: 12px;
    background: #2563eb;
    color: #fff;
    font: inherit;
    font-weight: 700;
    cursor: pointer;
}
.ariya-qw-selected {
    margin-top: 24px;
    padding: 18px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 13px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.ariya-qw-selected strong {
    color: #1d4ed8;
    font-size: 19px;
}
.ariya-qw-note {
    margin-top: 22px;
    color: #64748b;
    line-height: 2;
}
.ariya-qw-error {
    padding: 12px;
    margin-bottom: 15px;
    background: #fef2f2;
    color: #991b1b;
    border-radius: 10px;
}
@@media (max-width: 600px) {
    .ariya-qw-page { padding-top: 12px; }
    .ariya-qw-card { padding: 19px 15px; }
    .ariya-qw-card h1 { font-size: 21px; }
    .ariya-qw-form button { width: 100%; }
}
</style>
@endpush

@section('content')
<main class="main-content dt-sl ariya-qw-page">
    <div class="container main-container">
        <div class="ariya-qw-wrap">

            <nav class="ariya-qw-breadcrumb" aria-label="مسیر صفحه">
                <a href="{{ route('front.index') }}">خانه</a>
                <span> / خرید سریع عمده</span>
            </nav>

            <section class="ariya-qw-card">

                <h1>خرید سریع عمده</h1>

                <p class="ariya-qw-description">
                    مدل گوشی خود را انتخاب کنید.
                    انتخاب شما برای مراجعه بعدی به این صفحه حفظ می‌شود.
                </p>

                @if($error)
                    <div class="ariya-qw-error" role="alert">
                        {{ $error }}
                    </div>
                @endif

                <form
                    class="ariya-qw-form"
                    method="GET"
                    action="{{ route('front.quick-wholesale.index') }}"
                >
                    <label for="ariya-qw-model">
                        مدل گوشی
                    </label>

                    <select id="ariya-qw-model" name="model" required>
                        @foreach($options as $key => $label)
                            <option
                                value="{{ $label }}"
                                {{ $selected === $key ? 'selected' : '' }}
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit">
                        تأیید مدل گوشی
                    </button>
                </form>

                <div
                    class="ariya-qw-selected"
                    data-selected-model-key="{{ $selected }}"
                >
                    <span>مدل انتخاب‌شده:</span>
                    <strong dir="ltr">{{ $selectedLabel }}</strong>
                </div>

                {{-- ARIYA_TASK01_STAGE05C_VIEW_LINK_V1 --}}
                @include('front::products.partials.quick-wholesale-variants', ['items' => $catalogItems])
                <p class="ariya-qw-note">
                    برای جلوگیری از خطای قیمت یا موجودی، همه انتخاب‌ها هنگام افزودن
                    یک‌بار دیگر در سرور بررسی می‌شوند.
                </p>

            </section>
        </div>
    </div>
</main>
@endsection

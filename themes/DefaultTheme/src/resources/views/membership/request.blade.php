@extends('front::auth.layouts.master', ['title' => 'درخواست عضویت'])

@php
    $selectedProvince = old('province_id', $previousMembership?->province_id);
    $selectedCity = old('city_id', $previousMembership?->city_id);
    $fullName = old('full_name', $previousMembership?->full_name);
    $citiesJson = $cities->map(function ($items) {
        return $items->map(fn ($city) => [
            'id' => $city->id,
            'name' => $city->name,
            'province_id' => $city->province_id,
        ])->values();
    });
@endphp

@push('styles')
<style>
    .membership-request-page { direction: rtl; padding: 12px 0 36px; }
    .membership-request-page *, .membership-request-page *::before, .membership-request-page *::after { box-sizing: border-box; }
    .membership-request-page .membership-request-shell { width: 100%; max-width: 752px; margin: 0 auto; padding: 0 16px; }
    .membership-request-page .membership-request-card { overflow: hidden; background: #fff; border: 1px solid #e8edf3; border-radius: 18px; box-shadow: 0 10px 35px rgba(24, 50, 83, .08); }
    .membership-request-page .membership-request-header { padding: 30px 34px 24px; text-align: center; border-bottom: 1px solid #eef1f5; }
    .membership-request-page .membership-request-title { margin: 0 0 9px; color: #223047; font-size: 24px; font-weight: 700; line-height: 1.6; }
    .membership-request-page .membership-request-subtitle { margin: 0; color: #6f7d90; font-size: 14px; line-height: 2; }
    .membership-request-page .membership-request-body { padding: 28px 34px 32px; }
    .membership-request-page .membership-alert { margin-bottom: 24px; padding: 15px 17px; border: 1px solid #f3d59b; border-radius: 12px; background: #fffaf0; color: #78571d; font-size: 14px; line-height: 2; }
    .membership-request-page .membership-alert--danger { border-color: #f2c8ca; background: #fff5f5; color: #9b3338; }
    .membership-request-page .membership-alert strong { color: inherit; }
    .membership-request-page .membership-field { margin-bottom: 21px; }
    .membership-request-page .membership-field-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .membership-request-page .membership-label { display: block; margin: 0 0 9px; color: #344156; font-size: 14px; font-weight: 600; line-height: 1.7; }
    .membership-request-page .membership-required { color: #d84b55; }
    .membership-request-page .membership-control { display: block; width: 100%; height: 50px; margin: 0; padding: 0 14px; color: #344156; font-family: inherit; font-size: 14px; background: #fff; border: 1px solid #dce3eb; border-radius: 10px; outline: 0; transition: border-color .2s ease, box-shadow .2s ease; }
    .membership-request-page select.membership-control { cursor: pointer; }
    .membership-request-page .membership-control:focus { border-color: #4d78ae; box-shadow: 0 0 0 3px rgba(9, 71, 152, .1); }
    .membership-request-page .membership-control[readonly], .membership-request-page .membership-control:disabled { color: #69778a; background: #f5f7fa; cursor: not-allowed; }
    .membership-request-page .membership-control--ltr { direction: ltr; text-align: right; }
    .membership-request-page .membership-control--invalid { border-color: #dd6970; }
    .membership-request-page .membership-error { display: block; margin-top: 7px; color: #c54149; font-size: 12px; line-height: 1.8; }
    .membership-request-page .membership-upload { position: relative; display: flex; align-items: center; min-height: 132px; padding: 20px; border: 1px dashed #9fb4ce; border-radius: 13px; background: #f8fafc; cursor: pointer; transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease; }
    .membership-request-page .membership-upload:hover, .membership-request-page .membership-upload:focus-within { border-color: #094798; background: #f4f8fd; box-shadow: 0 0 0 3px rgba(9, 71, 152, .08); }
    .membership-request-page .membership-upload--invalid { border-color: #dd6970; background: #fff9f9; }
    .membership-request-page .membership-upload-input { position: absolute; z-index: 2; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .membership-request-page .membership-upload-icon { display: inline-flex; flex: 0 0 52px; align-items: center; justify-content: center; width: 52px; height: 52px; margin-left: 16px; color: #094798; font-size: 29px; background: #eaf2fb; border-radius: 14px; }
    .membership-request-page .membership-upload-content { min-width: 0; }
    .membership-request-page .membership-upload-title { display: block; margin-bottom: 6px; color: #2c3b51; font-size: 14px; font-weight: 700; line-height: 1.8; }
    .membership-request-page .membership-upload-help, .membership-request-page .membership-upload-formats { display: block; color: #758397; font-size: 12px; line-height: 1.9; }
    .membership-request-page .membership-upload-formats { margin-top: 2px; color: #8b96a6; }
    .membership-request-page .membership-file-name { display: none; margin-top: 8px; color: #094798; font-size: 12px; font-weight: 600; overflow-wrap: anywhere; }
    .membership-request-page .membership-file-name.is-visible { display: block; }
    .membership-request-page .membership-previous-document { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 14px; padding: 15px 17px; border: 1px solid #e3e8ef; border-radius: 11px; background: #fff; }
    .membership-request-page .membership-previous-title { margin: 0 0 4px; color: #3b485b; font-size: 13px; font-weight: 700; }
    .membership-request-page .membership-previous-note { margin: 0; color: #7a8798; font-size: 12px; line-height: 1.8; }
    .membership-request-page .membership-previous-link { position: relative; z-index: 3; flex: 0 0 auto; padding: 8px 12px; color: #094798; font-size: 12px; font-weight: 600; white-space: nowrap; border: 1px solid #b9cee7; border-radius: 8px; }
    .membership-request-page .membership-previous-link:hover { color: #063876; background: #f2f7fc; }
    .membership-request-page .membership-submit { min-height: 51px; margin-top: 7px; border-radius: 10px; font-family: inherit; font-size: 14px; font-weight: 700; }
    .membership-request-page .membership-login-link { margin: 18px 0 0; text-align: center; font-size: 13px; }
    .membership-request-page .membership-login-link a { color: #66758a; }
    .membership-request-page .membership-login-link a:hover { color: #094798; }

    @media (max-width: 767.98px) {
        .membership-request-page { padding-top: 4px; }
        .membership-request-page .membership-request-card { border-radius: 15px; }
        .membership-request-page .membership-request-header { padding: 24px 20px 19px; }
        .membership-request-page .membership-request-title { font-size: 21px; }
        .membership-request-page .membership-request-body { padding: 23px 20px 26px; }
        .membership-request-page .membership-field-grid { grid-template-columns: minmax(0, 1fr); gap: 0; }
        .membership-request-page .membership-upload { align-items: flex-start; padding: 17px; }
        .membership-request-page .membership-upload-icon { flex-basis: 46px; width: 46px; height: 46px; margin-left: 12px; font-size: 25px; }
        .membership-request-page .membership-previous-document { align-items: flex-start; flex-direction: column; }
    }

    @media (max-width: 359.98px) {
        .membership-request-page .membership-request-shell { padding: 0 12px; }
        .membership-request-page .membership-request-header, .membership-request-page .membership-request-body { padding-right: 17px; padding-left: 17px; }
        .membership-request-page .membership-upload { display: block; }
        .membership-request-page .membership-upload-icon { margin: 0 0 12px; }
    }
</style>
@endpush

@section('content')
<main class="main-content dt-sl membership-request-page">
    <div class="membership-request-shell">
        <section class="membership-request-card" aria-labelledby="membership-request-title">
            <header class="membership-request-header">
                <h1 id="membership-request-title" class="membership-request-title">درخواست عضویت</h1>
                <p class="membership-request-subtitle">برای عضویت در آریا جانبی، اطلاعات فروشگاه خود را تکمیل کنید.</p>
            </header>

            <div class="membership-request-body">
                @if($previousMembership)
                    <div class="membership-alert" role="alert">
                        <strong>درخواست قبلی شما تأیید نشده است.</strong>
                        @if($previousMembership->reject_reason)
                            <div><strong>دلیل:</strong> {{ $previousMembership->reject_reason }}</div>
                        @endif
                        <div>اطلاعات موردنیاز را اصلاح کرده و درخواست را دوباره ارسال کنید.</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="membership-alert membership-alert--danger" role="alert">لطفاً خطاهای مشخص‌شده در فرم را بررسی کنید.</div>
                @endif

                <form action="{{ route('membership.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="membership-field">
                        <label class="membership-label" for="membership-full-name">نام و نام خانوادگی <span class="membership-required">*</span></label>
                        <input id="membership-full-name" type="text" name="full_name" class="membership-control @error('full_name') membership-control--invalid @enderror" value="{{ $fullName }}" autocomplete="name" placeholder="مثلاً محمد رضایی" required>
                        @error('full_name')<span class="membership-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="membership-field">
                        <label class="membership-label" for="membership-mobile">شماره موبایل <span class="membership-required">*</span></label>
                        <input id="membership-mobile" type="text" class="membership-control membership-control--ltr" value="{{ $mobile }}" readonly aria-readonly="true">
                        <input type="hidden" name="mobile" value="{{ $mobile }}">
                        @error('mobile')<span class="membership-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="membership-field-grid">
                        <div class="membership-field">
                            <label class="membership-label" for="membership-province">استان <span class="membership-required">*</span></label>
                            <select id="membership-province" name="province_id" class="membership-control @error('province_id') membership-control--invalid @enderror" required>
                                <option value="">انتخاب استان</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}" {{ (string) $selectedProvince === (string) $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                                @endforeach
                            </select>
                            @error('province_id')<span class="membership-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="membership-field">
                            <label class="membership-label" for="membership-city">شهر <span class="membership-required">*</span></label>
                            <select id="membership-city" name="city_id" class="membership-control @error('city_id') membership-control--invalid @enderror" data-selected="{{ $selectedCity }}" required disabled>
                                <option value="">ابتدا استان را انتخاب کنید</option>
                            </select>
                            @error('city_id')<span class="membership-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="membership-field">
                        <span class="membership-label">تصویر جواز کسب یا فروشگاه <span class="membership-required">*</span></span>
                        <label class="membership-upload @error('document_image') membership-upload--invalid @enderror" for="membership-document-image">
                            <input id="membership-document-image" type="file" name="document_image" class="membership-upload-input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" {{ $previousMembership?->document_image ? '' : 'required' }}>
                            <span class="membership-upload-icon" aria-hidden="true"><i class="mdi mdi-cloud-upload-outline"></i></span>
                            <span class="membership-upload-content">
                                <span class="membership-upload-title">تصویر جواز کسب یا فروشگاه</span>
                                <span class="membership-upload-help">بارگذاری حداقل یک تصویر واضح از جواز کسب، سردر فروشگاه یا نمای فروشگاه الزامی است.</span>
                                <span class="membership-upload-formats">فرمت‌های مجاز: JPG، JPEG، PNG و WEBP — حداکثر حجم ۵ مگابایت</span>
                                <span id="membership-file-name" class="membership-file-name" aria-live="polite"></span>
                            </span>
                        </label>
                        @error('document_image')<span class="membership-error">{{ $message }}</span>@enderror

                        @if($previousMembership?->document_image)
                            <div class="membership-previous-document">
                                <div>
                                    <p class="membership-previous-title">تصویر قبلی ثبت شده</p>
                                    <p class="membership-previous-note">در صورت انتخاب نکردن تصویر جدید، تصویر قبلی استفاده خواهد شد.</p>
                                </div>
                                @if($previousDocumentUrl = $previousMembership->documentImageUrl())
    <a href="{{ $previousDocumentUrl }}" target="_blank" rel="noopener" class="membership-previous-link">مشاهده تصویر قبلی</a>
@endif
                            </div>
                        @endif
                    </div>

                    <button type="submit" class="btn-primary-cm btn-with-icon w-100 membership-submit">
                        <i class="mdi mdi-account-plus-outline"></i>
                        ثبت درخواست عضویت
                    </button>
                    <p class="membership-login-link"><a href="{{ route('login') }}">بازگشت به صفحه ورود</a></p>
                </form>
            </div>
        </section>
    </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
    var cities = @json($citiesJson);
    var provinceSelect = document.getElementById('membership-province');
    var citySelect = document.getElementById('membership-city');
    var fileInput = document.getElementById('membership-document-image');
    var fileName = document.getElementById('membership-file-name');

    function loadCities() {
        var provinceId = provinceSelect.value;
        var selectedCity = String(citySelect.dataset.selected || '');
        citySelect.innerHTML = '';

        if (!provinceId || !cities[provinceId]) {
            citySelect.disabled = true;
            citySelect.appendChild(new Option('ابتدا استان را انتخاب کنید', ''));
            return;
        }

        citySelect.disabled = false;
        citySelect.appendChild(new Option('انتخاب شهر', ''));
        cities[provinceId].forEach(function (city) {
            var option = new Option(city.name, city.id);
            option.selected = String(city.id) === selectedCity;
            citySelect.appendChild(option);
        });
    }

    if (provinceSelect && citySelect) {
        provinceSelect.addEventListener('change', function () {
            citySelect.dataset.selected = '';
            loadCities();
        });
        loadCities();
    }

    if (fileInput && fileName) {
        fileInput.addEventListener('change', function () {
            var selectedFile = fileInput.files && fileInput.files[0];
            fileName.textContent = selectedFile ? 'فایل انتخاب‌شده: ' + selectedFile.name : '';
            fileName.classList.toggle('is-visible', Boolean(selectedFile));
        });
    }
})();
</script>
@endpush

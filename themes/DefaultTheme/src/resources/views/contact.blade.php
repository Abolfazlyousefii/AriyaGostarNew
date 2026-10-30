@extends('front::layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ theme_asset('mapp/css/mapp.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('mapp/css/fa/style.css') }}">
@endpush

@push('meta')

@if(option('allow_indexing_contact') == "off" )
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="bingbot" content="noindex, nofollow">

@else
    <meta name="robots" content="index, follow"/>
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
@endif
<link rel="canonical" href="{{ route('front.contact.index') }}" />
@endpush

@section('content')

    <!-- Start main-content -->
    <main class="main-content dt-sl mt-4 mb-3">
        <div class="container main-container">

            <div class="row">
                <div class="col-12">
                    <div class="page dt-sl dt-sn pt-3 pb-5">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-ui additional-info dt-sl">
                                    <form id="contact-form" action="{{ route('front.contact.store') }}" method="POST">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-row-title">
                                                    <h3>{{ trans('front::messages.contact-us.fname-and-lname') }}</h3>
                                                </div>
                                                <div class="form-row form-group">
                                                    <input type="text" class="input-ui pr-2" name="name">
                                                </div>
                                            </div>
                                            <div class="col-lg-12 mt-4">
                                                <div class="form-row-title">
                                                    <h3>شماره تماس</h3>
                                                </div>
                                                <div class="form-row form-group">
                                                    <input type="mobile" class="input-ui pl-2 text-left dir-ltr" name="mobile">
                                                </div>
                                            </div>
                                            <div class="col-lg-12 mt-4">
                                                <div class="form-row-title">
                                                    <h3>{{ trans('front::messages.contact-us.topic') }}</h3>
                                                </div>
                                                <div class="form-row form-group">
                                                    <input type="text" class="input-ui pl-2"  name="subject">
                                                </div>
                                            </div>

                                            <div class="col-lg-12 mt-4">
                                                <div class="form-row-title">
                                                    <h4>
                                                      {{ trans('front::messages.contact-us.message-text') }}

                                                    </h4>
                                                </div>
                                                <div class="form-row form-group">
                                                    <textarea rows="10" name="message" class="input-ui pr-2 text-right"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-row mt-4">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <input type="text" class="input-ui pl-2 captcha" autocomplete="off" name="captcha" placeholder="{{ trans('front::messages.contact-us.security-code') }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <img class="captcha" src="{{ captcha_src('flat') }}" alt="captcha">
                                            </div>
                                        </div>

                                        <div class="form-row mt-3 justify-content-center">
                                            <button id="submit-btn" type="submit" class="btn-primary-cm btn-with-icon ml-2">
                                                <i class="mdi mdi-message"></i>
                                                 {{ trans('front::messages.contact-us.send-message') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div id="map"></div>
                            </div>
                        </div>
                        <div class="row row-deck">
                            <div class="col-md-4">
                                <div class="contact_tile block">
                                    <span class="tiles__icon icon-location-pin"></span>
                                    <h6 class="tiles__title">{{ trans('front::messages.contact-us.address') }}</h6>
                                    <div class="tiles__content">
                                        <p>{{ option('info_address') }}</p>
                                    </div>
                                </div>
                            </div>
                            <!-- end /.col-md-4 -->

                            <div class="col-md-4">
                                <div class="contact_tile block">
                                    <span class="tiles__icon icon-earphones"></span>
                                    <h6 class="tiles__title">{{ trans('front::messages.contact-us.phone-number') }}</h6>
                                    <div class="tiles__content">
                                        <p>{{ option('info_tel') }}</p>
                                    </div>
                                </div>
                                <!-- end /.contact_tile block -->
                            </div>
                            <!-- end /.col-md-4 -->

                            <div class="col-md-4">
                                <div class="contact_tile block">
                                    <span class="tiles__icon icon-envelope-open"></span>
                                    <h6 class="tiles__title">{{ trans('front::messages.contact-us.email-address') }}</h6>
                                    <div class="tiles__content">
                                        <p>{{ option('info_email') }}</p>
                                    </div>
                                </div>
                                <!-- end /.contact_tile -->
                            </div>
                            <!-- end /.col-md-4 -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
    <!-- End main-content -->

    <!-- end /.map -->

    <!--================================
        END BREADCRUMB AREA
    =================================-->

@endsection

@push('scripts')
    <script src="{{ theme_asset('js/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ theme_asset('js/plugins/jquery-validation/localization/messages_fa.min.js') }}?v=2"></script>

    <script type="text/javascript" src="{{ theme_asset('mapp/js/mapp.env.js') }}"></script>
    <script type="text/javascript" src="{{ theme_asset('mapp/js/mapp.min.js?v=1') }}"></script>

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDdbCAXvJIl7CKZwfpTswAIHvqJmZTUPwQ"></script>

    <script>
        var info_map_type = "{{ option('info_map_type', 'google') }}"
        var info_latitude = "{{ option('info_latitude', '38.07709880960678') }}";
        var info_Longitude = "{{ option('info_Longitude', '46.28582686185837') }}";
        var info_site_title = "{{ option('info_site_title', 'لاراول شاپ') }}";

        var mapIrApiKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjYwMTBjYWE1OWU4ZDAyYzM0YWI2MGFhZDE5MTBhNjM5ZTZkYTI0MzA1ZmMwNzQzY2NmMjRkZmQ2Y2FlMzFjOThmODg4MjExYWY4ZDkwMGE1In0.eyJhdWQiOiIxMjcxOSIsImp0aSI6IjYwMTBjYWE1OWU4ZDAyYzM0YWI2MGFhZDE5MTBhNjM5ZTZkYTI0MzA1ZmMwNzQzY2NmMjRkZmQ2Y2FlMzFjOThmODg4MjExYWY4ZDkwMGE1IiwiaWF0IjoxNjEyODY3Mjc2LCJuYmYiOjE2MTI4NjcyNzYsImV4cCI6MTYxNTM3Mjg3Niwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.QNujb2BIyM8mIMy2AhivkMTpVCRyanpUIifJguxoEe4hXB1MESD2CWnO0WPq854Bi6yQyfD2w-oqjOi5N1aZmX4prggmrYelHy_mC1JEwAhWien_6QviFAvkhGDC-aPW4zjFKG2REUkQzXaeL2em543P6-hWdjFaUVSibm1XL4_CUnjJiafQsMQ67ZJ5E7Cpk92L89nJ0LMaBocex56tRqz7_7wZQUAtDYjfal90h2XaGh3QZ2rMwl69ZfMTrOEeTM9O6YCynT3IoTpDnNSXExJeMDuGv4zCD37UYG1gpVtNfipwgvc2J_LzLMXS4rnVAV2ednLKEYu7-jUXr68psg';
    </script>

    <script src="{{ theme_asset('js/pages/contact.js') }}?v=3"></script>
@endpush


{{-- ARIYA_CONTACT_REDESIGN_V1: view-only. Keep original form, captcha, routes and scripts. --}}
@push('styles')
<style id="ariya-contact-redesign-v1-css">
/* ARIYA_CONTACT_REDESIGN_V1 — scoped to the contact page only. */
.ariya-contact-v1 {
  --ac-blue: #1367d2;
  --ac-dark: #102d50;
  --ac-muted: #677e96;
  --ac-border: #e4edf7;
  --ac-soft: #f5f9fe;
  font-family: inherit;
  color: var(--ac-dark);
}
.ariya-contact-v1 .main-container { max-width: 1250px; }
.ariya-contact-v1 .page {
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
  padding: 0 !important;
}
.ariya-contact-hero {
  position: relative;
  overflow: hidden;
  padding: 42px 44px;
  margin: 0 0 26px;
  border-radius: 24px;
  background: linear-gradient(115deg, #0b315f 0%, #11519c 58%, #1376d9 100%);
  color: #fff;
  box-shadow: 0 16px 35px rgba(13, 60, 117, .13);
}
.ariya-contact-hero::after {
  content: '';
  position: absolute;
  left: -75px;
  top: -120px;
  width: 350px;
  height: 350px;
  border: 1px solid rgba(255,255,255,.18);
  border-radius: 50%;
  box-shadow: 0 0 0 38px rgba(255,255,255,.035), 0 0 0 90px rgba(255,255,255,.025);
  pointer-events: none;
}
.ariya-contact-hero__inner { position: relative; z-index: 1; max-width: 780px; }
.ariya-contact-hero__eyebrow {
  display: inline-flex;
  padding: 7px 13px;
  border: 1px solid rgba(255,255,255,.32);
  border-radius: 99px;
  font-size: 12px;
  font-weight: 700;
  background: rgba(255,255,255,.11);
  margin-bottom: 15px;
}
.ariya-contact-hero h1 {
  margin: 0 0 13px;
  color: #fff;
  font-size: clamp(24px, 3vw, 35px);
  line-height: 1.6;
  font-weight: 850;
}
.ariya-contact-hero p {
  color: rgba(255,255,255,.91);
  margin: 0;
  max-width: 660px;
  font-size: 14px;
  line-height: 2;
}
.ariya-contact-v1 .ariya-contact-primary-row {
  display: grid !important;
  grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
  gap: 22px;
  margin: 0 0 22px !important;
  align-items: stretch;
}
.ariya-contact-v1 .ariya-contact-primary-row > * {
  width: 100% !important;
  flex: initial !important;
  max-width: none !important;
  min-width: 0;
  padding: 0 !important;
}
.ariya-contact-v1 .ariya-contact-form-column,
.ariya-contact-v1 .ariya-contact-side-column { min-width: 0; }
.ariya-contact-v1 .ariya-contact-form-card {
  box-sizing: border-box;
  height: 100%;
  padding: 30px;
  border-radius: 22px;
  background: #fff;
  border: 1px solid var(--ac-border);
  box-shadow: 0 7px 28px rgba(24,60,99,.055);
}
.ariya-contact-form-heading { margin: 0 0 23px; padding-bottom: 18px; border-bottom: 1px solid var(--ac-border); }
.ariya-contact-form-heading__title {
  font-size: 20px;
  line-height: 1.7;
  font-weight: 800;
  margin: 0 0 5px;
  color: var(--ac-dark);
}
.ariya-contact-form-heading__subtitle { margin: 0; font-size: 12.5px; line-height: 2; color: var(--ac-muted); }
.ariya-contact-v1 #contact-form { text-align: right; direction: rtl; }
.ariya-contact-v1 #contact-form .row { margin-right: -8px; margin-left: -8px; }
.ariya-contact-v1 #contact-form .row > [class*='col-'] { padding-right: 8px; padding-left: 8px; }
.ariya-contact-v1 #contact-form .col-lg-12.mt-4 { margin-top: 13px !important; }
.ariya-contact-v1 #contact-form .form-row-title { margin: 0 0 8px; }
.ariya-contact-v1 #contact-form .form-row-title h3,
.ariya-contact-v1 #contact-form .form-row-title h4,
.ariya-contact-v1 #contact-form label {
  color: #2a405a;
  font-size: 13px !important;
  font-weight: 700;
  line-height: 1.8;
  margin: 0;
}
.ariya-contact-v1 #contact-form .form-row,
.ariya-contact-v1 #contact-form .form-group { margin-bottom: 0; }
.ariya-contact-v1 #contact-form .input-ui,
.ariya-contact-v1 #contact-form input:not([type='hidden']):not([type='checkbox']):not([type='radio']),
.ariya-contact-v1 #contact-form textarea {
  width: 100%;
  max-width: 100%;
  background: #f9fbff;
  color: #243d58;
  border: 1px solid #dce6f2;
  border-radius: 11px;
  box-shadow: none;
  outline: 0;
  transition: border-color .2s, box-shadow .2s, background .2s;
  font-family: inherit;
  font-size: 14px;
  box-sizing: border-box;
}
.ariya-contact-v1 #contact-form input:not([type='hidden']):not([type='checkbox']):not([type='radio']) {
  min-height: 46px;
  padding: 10px 13px;
}
.ariya-contact-v1 #contact-form textarea { min-height: 126px; padding: 12px 14px; resize: vertical; }
.ariya-contact-v1 #contact-form input:focus,
.ariya-contact-v1 #contact-form textarea:focus {
  border-color: #4990e6;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(19,103,210,.10);
}
.ariya-contact-v1 #contact-form input[name='mobile'] { direction: ltr; text-align: left; }
.ariya-contact-v1 #contact-form img.captcha {
  border: 1px solid var(--ac-border);
  border-radius: 9px;
  max-width: 100%;
  min-height: 44px;
  background: #fff;
  object-fit: contain;
}
.ariya-contact-v1 #contact-form .btn-primary-cm,
.ariya-contact-v1 #contact-form button[type='submit'] {
  min-height: 47px;
  padding: 10px 28px;
  background: var(--ac-blue);
  border: 0;
  color: #fff;
  border-radius: 11px;
  font-weight: 800;
  font-size: 14px;
  box-shadow: 0 7px 16px rgba(19,103,210,.15);
  transition: background .2s, transform .2s;
}
.ariya-contact-v1 #contact-form button[type='submit']:hover { background: #0d4d9c; transform: translateY(-1px); }
.ariya-contact-v1 .ariya-contact-side-column { display: flex; flex-direction: column; gap: 14px; }
.ariya-contact-side-panel {
  padding: 27px;
  border-radius: 22px;
  background: #fff;
  border: 1px solid var(--ac-border);
  box-shadow: 0 7px 28px rgba(24,60,99,.05);
}
.ariya-contact-side-panel__icon {
  width: 46px;
  height: 46px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  margin-bottom: 14px;
  background: #e8f2ff;
  color: var(--ac-blue);
  font-size: 23px;
}
.ariya-contact-side-panel h2 {
  margin: 0 0 10px;
  color: var(--ac-dark);
  font-size: 18px;
  font-weight: 800;
  line-height: 1.7;
}
.ariya-contact-side-panel p { margin: 0; color: var(--ac-muted); line-height: 2.1; font-size: 13px; }
.ariya-contact-v1 #map {
  min-height: 200px;
  width: 100%;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid var(--ac-border);
  background: linear-gradient(140deg, #e7f1fe, #f8fbff);
}
.ariya-contact-v1 #map:empty::before {
  content: 'موقعیت فروشگاه روی نقشه';
  display: flex;
  min-height: 200px;
  align-items: center;
  justify-content: center;
  color: #637b96;
  font-size: 13px;
}
.ariya-contact-v1 .row-deck { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 15px; margin: 5px 0 25px; }
.ariya-contact-v1 .row-deck > [class*='col-'] { width: 100%; max-width: none; flex: initial; padding: 0; min-width: 0; }
.ariya-contact-v1 .contact_tile {
  height: 100%;
  min-height: 144px;
  padding: 23px 20px;
  border-radius: 18px;
  border: 1px solid var(--ac-border);
  background: #fff;
  box-shadow: 0 4px 19px rgba(25,66,115,.04);
}
.ariya-contact-v1 .contact_tile .tiles__title { font-weight: 800; color: var(--ac-dark); font-size: 14px; }
.ariya-contact-v1 .contact_tile .tiles__content,
.ariya-contact-v1 .contact_tile .tiles__content p { overflow-wrap: anywhere; line-height: 2; color: var(--ac-muted); font-size: 13px; }
.ariya-contact-v1 .contact_tile .tiles__icon { color: var(--ac-blue); font-size: 23px; }
@media (max-width: 991px) {
  .ariya-contact-v1 .ariya-contact-primary-row { grid-template-columns: minmax(0,1fr); gap: 16px; }
  .ariya-contact-v1 .row-deck { grid-template-columns: repeat(2,minmax(0,1fr)); }
  .ariya-contact-hero { padding: 34px 26px; }
}
@media (max-width: 620px) {
  .ariya-contact-hero { border-radius: 16px; padding: 26px 20px; margin-bottom: 15px; }
  .ariya-contact-hero h1 { font-size: 23px; }
  .ariya-contact-v1 .ariya-contact-form-card { padding: 20px 17px; border-radius: 16px; }
  .ariya-contact-v1 .ariya-contact-side-panel { padding: 19px; border-radius: 16px; }
  .ariya-contact-v1 .row-deck { grid-template-columns: 1fr; gap: 10px; }
  .ariya-contact-v1 #contact-form .form-row.mt-4 { margin-top: 17px !important; }
  .ariya-contact-v1 #contact-form button[type='submit'] { width: 100%; }
  .ariya-contact-v1 #map { min-height: 175px; }
}
@media (prefers-reduced-motion: reduce) {
  .ariya-contact-v1 #contact-form input,
  .ariya-contact-v1 #contact-form textarea,
  .ariya-contact-v1 #contact-form button { transition: none !important; }
}

</style>
@endpush

@push('scripts')
<script id="ariya-contact-redesign-v1-js">
/* ARIYA_CONTACT_REDESIGN_V1 — presentation enhancement only. */
(function () {
  'use strict';
  function init() {
    var form = document.getElementById('contact-form');
    if (!form) return;
    var main = form.closest('main') || form.closest('.main-content');
    if (!main || main.classList.contains('ariya-contact-v1')) return;
    main.classList.add('ariya-contact-v1');

    var page = form.closest('.page') || form.closest('.main-container');
    if (page && !page.querySelector('.ariya-contact-hero')) {
      var hero = document.createElement('section');
      hero.className = 'ariya-contact-hero';
      hero.setAttribute('aria-label', 'ارتباط با فروشگاه');
      hero.innerHTML = '<div class="ariya-contact-hero__inner">' +
        '<span class="ariya-contact-hero__eyebrow">ارتباط با آریا جانبی</span>' +
        '<h1>ما اینجا هستیم تا صدای شما را بشنویم</h1>' +
        '<p>سؤال، پیشنهاد یا درخواست پشتیبانی دارید؟ از طریق فرم تماس پیام بفرستید یا از راه‌های ارتباطی درج‌شده در همین صفحه استفاده کنید.</p>' +
        '</div>';
      page.insertBefore(hero, page.firstElementChild);
    }

    var formContainer = form.closest('.form-ui') || form.parentElement;
    if (formContainer) {
      formContainer.classList.add('ariya-contact-form-card');
      if (!formContainer.querySelector('.ariya-contact-form-heading')) {
        var heading = document.createElement('div');
        heading.className = 'ariya-contact-form-heading';
        heading.innerHTML = '<h2 class="ariya-contact-form-heading__title">پیام خود را برای ما بنویسید</h2>' +
          '<p class="ariya-contact-form-heading__subtitle">فیلدهای فرم را تکمیل کنید تا پیام شما به واحد مربوطه برسد.</p>';
        formContainer.insertBefore(heading, form);
      }
    }

    var formCol = form.closest('.col-lg-6');
    var map = document.getElementById('map');
    var mapCol = map ? map.closest('.col-lg-6') : null;
    if (formCol) formCol.classList.add('ariya-contact-form-column');
    if (mapCol && mapCol !== formCol) {
      mapCol.classList.add('ariya-contact-side-column');
      if (!mapCol.querySelector('.ariya-contact-side-panel')) {
        var intro = document.createElement('section');
        intro.className = 'ariya-contact-side-panel';
        intro.innerHTML = '<div class="ariya-contact-side-panel__icon" aria-hidden="true">✉</div>' +
          '<h2>راه ارتباطی مناسب خودتان را انتخاب کنید</h2>' +
          '<p>برای ارسال پیام از فرم مقابل استفاده کنید. نشانی، شماره تماس و ایمیل فروشگاه نیز در همین صفحه در دسترس شماست.</p>';
        mapCol.insertBefore(intro, map);
      }
    }
    if (formCol && mapCol && formCol.parentElement === mapCol.parentElement) {
      formCol.parentElement.classList.add('ariya-contact-primary-row');
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();

</script>
@endpush

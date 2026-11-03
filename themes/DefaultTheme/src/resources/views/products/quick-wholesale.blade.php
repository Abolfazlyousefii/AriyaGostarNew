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
.ariya-model-search{position:relative;margin:10px 0 0}
.ariya-model-search[hidden],.ariya-model-results[hidden]{display:none!important}
.ariya-model-search input{width:100%;min-height:50px;box-sizing:border-box;padding:10px 14px;border:1px solid #cbd5e1;border-radius:12px;background:#f8fafc;font:inherit;color:#0f172a}
.ariya-model-results{position:absolute;z-index:40;right:0;left:0;top:100%;max-height:260px;overflow-y:auto;background:white;border:1px solid #cbd5e1;border-radius:12px;box-shadow:0 12px 22px #0f172a20;padding:5px}
.ariya-qw-form .ariya-model-results button{display:block;width:100%;margin:0;min-height:40px;padding:10px;text-align:right;border:0;background:white;color:#0f172a;border-radius:7px}
.ariya-qw-form .ariya-model-results button:hover,.ariya-qw-form .ariya-model-results button:focus{background:#eff6ff}
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
                                value="{{ $key }}"
                                {{ $selected === $key ? 'selected' : '' }}
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <div class="ariya-model-search" data-qw-model-search hidden>
                        <input type="search" id="ariya-model-query" placeholder="مدل گوشی را بنویسید؛ مثلاً آیفون ۱۳" autocomplete="off" maxlength="80" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ariya-model-results">
                        <div id="ariya-model-results" class="ariya-model-results" role="listbox" aria-label="مدل‌های پیشنهادی" hidden></div>
                    </div>

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

@push('scripts')
<script>
(function(){
'use strict';
var form=document.querySelector('.ariya-qw-form');
if(!form)return;
var select=form.querySelector('#ariya-qw-model');
var picker=form.querySelector('[data-qw-model-search]');
var input=form.querySelector('#ariya-model-query');
var list=form.querySelector('#ariya-model-results');
if(!select||!picker||!input||!list)return;
var options=Array.from(select.options).filter(function(o){return o.value;});
if(!options.length)return;
function norm(v){return String(v||'').toLowerCase()
.replace(/[۰-۹]/g,function(c){return String('۰۱۲۳۴۵۶۷۸۹'.indexOf(c));})
.replace(/[٠-٩]/g,function(c){return String('٠١٢٣٤٥٦٧٨٩'.indexOf(c));})
.replace(/(?:آی\s*فون|ای\s*فون|ايفون|iphone)/g,'iphone')
.replace(/پرو\s*مکس/g,'pro max').replace(/پرو/g,'pro')
.replace(/مینی/g,'mini').replace(/مکس/g,'max')
.replace(/سامسونگ/g,'samsung').replace(/شیائومی|شیاومی/g,'xiaomi')
.replace(/هوآوی|هواوی/g,'huawei').replace(/آنر|انر/g,'honor')
.replace(/ریلمی/g,'realme').replace(/گلکسی/g,'galaxy').replace(/ردمی/g,'redmi')
.replace(/\s+/g,' ').trim();}
var picked=select.value;
function close(){list.hidden=true;input.setAttribute('aria-expanded','false');}
function choose(o){picked=o.value;select.value=o.value;input.value=o.textContent.trim();input.setCustomValidity('');close();}
function render(){
list.textContent='';
var words=norm(input.value).split(' ').filter(Boolean);
var found=options.filter(function(o){var name=norm(o.textContent);return words.every(function(w){return name.indexOf(w)!==-1;});});
found.forEach(function(o){
var b=document.createElement('button');b.type='button';b.setAttribute('role','option');b.textContent=o.textContent.trim();
b.addEventListener('mousedown',function(e){e.preventDefault();});
b.addEventListener('click',function(){choose(o);});list.appendChild(b);
});
if(!found.length){var e=document.createElement('div');e.textContent='مدلی پیدا نشد';e.style.padding='10px';list.appendChild(e);}
list.hidden=false;input.setAttribute('aria-expanded','true');
}
input.value=select.selectedOptions.length?select.selectedOptions[0].textContent.trim():'';
input.addEventListener('focus',function(){input.select();render();});
input.addEventListener('input',function(){picked='';input.setCustomValidity('');render();});
input.addEventListener('keydown',function(e){
if(e.key==='Escape'){close();return;}
if(e.key==='Enter'&&!list.hidden){var first=list.querySelector('button');if(first){e.preventDefault();first.click();}}
});
document.addEventListener('click',function(e){if(!picker.contains(e.target))close();});
form.addEventListener('submit',function(e){
var found=options.find(function(o){return o.value===picked;});
if(!found||norm(input.value)!==norm(found.textContent)){
e.preventDefault();input.setCustomValidity('مدل را از پیشنهادها انتخاب کنید.');input.reportValidity();render();return;
}
select.value=picked;
});
select.style.display='none';picker.hidden=false;
})();
</script>
@endpush

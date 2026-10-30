{{-- ARIYA_DIRECT_DISCOUNT_EDITOR_V2: isolated per-variant form. --}}
@can('products.update')
@if($directDiscountProduct->isPhysical())
@php
  $ariyaDirectVariants = $directDiscountProduct->prices()->orderBy('id')->get();
@endphp
<section class="card mb-2" dir="rtl" id="ariya-direct-discount-editor" aria-label="تخفیف اختصاصی محصول">
  <div class="card-body">
    <h4 class="mb-1">تخفیف اختصاصی محصول</h4>
    <p class="text-muted mb-2">سه حالت برای هر تنوع: درصد، مبلغ ثابت تخفیف یا قیمت نهایی ثابت. مبالغ به تومان هستند. مقدار صفر در حالت درصد یا مبلغ تخفیف، آن را حذف می‌کند. قیمت پایه و موجودی انبار تغییر نمی‌کنند.</p>
    @if(session('ariya_direct_discount_saved'))
      <div class="alert alert-success" role="status">{{ session('ariya_direct_discount_saved') }}</div>
    @endif
    @if($errors->has('price_id') || $errors->has('mode') || $errors->has('value') || $errors->has('discount_expire_at'))
      <div class="alert alert-danger" role="alert">
        @foreach(['price_id','mode','value','discount_expire_at'] as $field)
          @foreach($errors->get($field) as $message)<div>{{ $message }}</div>@endforeach
        @endforeach
      </div>
    @endif
    @if($ariyaDirectVariants->count() > 1)
      @php
        $ariyaBulkRegulars=$ariyaDirectVariants->map(fn($v)=>(int)round($v->regularPrice()));
        $ariyaBulkMin=(int)$ariyaBulkRegulars->min();
        $ariyaBulkMax=(int)$ariyaBulkRegulars->max();
      @endphp
      <form method="POST" action="{{ route('admin.products.direct-discount.apply-all',['product'=>$directDiscountProduct->id]) }}" class="border border-primary rounded p-3 mb-3 ariya-dd-apply-all" autocomplete="off" data-min-regular="{{ $ariyaBulkMin }}" data-max-regular="{{ $ariyaBulkMax }}" onsubmit="if(this.dataset.ariyaBusy==='1')return false;if(!confirm('تخفیف اختصاصی همه تنوع‌های این محصول جایگزین شود؟'))return false;this.dataset.ariyaBusy='1';">
        @csrf
        <h5 class="mb-1">اعمال یک تخفیف روی تمام تنوع‌های این محصول</h5>
        <p class="text-muted small mb-2">با یک بار ثبت، تخفیف همه {{ number_format($ariyaDirectVariants->count()) }} تنوع فعال این محصول جایگزین می‌شود. صفر در حالت درصد یا مبلغ تخفیف یعنی حذف تخفیف همه تنوع‌ها. قیمت پایه و موجودی انبار تغییر نمی‌کند.</p>
        <div class="row align-items-end">
          <div class="col-lg-3 col-md-6 mb-2">
            <label>نوع تخفیف برای همه</label>
            <select name="mode" class="form-control ariya-dd-bulk-mode" required>
              <option value="percent">درصد تخفیف</option>
              <option value="fixed_amount">مبلغ ثابت تخفیف (تومان)</option>
              <option value="fixed_final">قیمت نهایی ثابت (تومان)</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-6 mb-2">
            <label class="ariya-dd-bulk-label">درصد تخفیف</label>
            <input type="number" name="value" class="form-control ariya-dd-bulk-value" value="0" min="0" max="99" step="1" required>
          </div>
          <div class="col-lg-4 col-md-6 mb-2">
            <label>پایان تخفیف (شمسی؛ اختیاری)</label>
            <input type="text" name="discount_expire_at" class="form-control ariya-dd-jalali" dir="ltr" maxlength="24" placeholder="۱۴۰۵/۰۸/۳۰ ۲۳:۵۹">
          </div>
          <div class="col-lg-3 col-md-6 mb-2">
            <button type="submit" class="btn btn-success btn-block ariya-dd-bulk-submit">اعمال تخفیف روی همه تنوع‌ها</button>
          </div>
        </div>
        <div class="small ariya-dd-bulk-preview" aria-live="polite"></div>
        <div class="small text-muted">کمترین قیمت پایه: {{ number_format($ariyaBulkMin) }} تومان؛ بیشترین: {{ number_format($ariyaBulkMax) }} تومان. مبلغ ثابت تخفیف و قیمت نهایی ثابت باید برای ارزان‌ترین تنوع هم معتبر باشند.</div>
      </form>
      @if(session('ariya_bulk_direct_discount_saved'))
        <div class="alert alert-success" role="status">{{ session('ariya_bulk_direct_discount_saved') }}</div>
      @endif
      @if($errors->has('mode') || $errors->has('value') || $errors->has('discount_expire_at'))
        <div class="alert alert-danger" role="alert">
          @foreach(['mode','value','discount_expire_at'] as $field)
            @foreach($errors->get($field) as $message)<div>{{ $message }}</div>@endforeach
          @endforeach
        </div>
      @endif
      <details class="mb-3">
        <summary class="p-2 border rounded" style="cursor:pointer">ویرایش جداگانه تنوع‌ها (اختیاری) — {{ number_format($ariyaDirectVariants->count()) }} تنوع</summary>
        <div class="pt-2">
    @endif
    @forelse($ariyaDirectVariants as $ariyaVariant)
      @php
        $ariyaExpires=$ariyaVariant->discount_expire_at;
        $ariyaExpiryValue=$ariyaExpires ? \Morilog\Jalali\Jalalian::fromCarbon($ariyaExpires)->format('Y/m/d H:i') : '';
        $ariyaMode=in_array($ariyaVariant->direct_discount_mode,['fixed_amount','fixed_final'],true) ? $ariyaVariant->direct_discount_mode : 'percent';
        $ariyaValue=$ariyaMode==='percent' ? (int)$ariyaVariant->getRawOriginal('discount') : (int)$ariyaVariant->direct_discount_value;
        $ariyaRegular=(int)round($ariyaVariant->regularPrice());
      @endphp
      <form method="POST" action="{{ route('admin.products.direct-discount.update',['product'=>$directDiscountProduct->id]) }}" class="border rounded p-2 mb-2 ariya-dd-three-form" data-regular-toman="{{ $ariyaRegular }}" autocomplete="off" onsubmit="if(this.dataset.ariyaBusy==='1')return false;this.dataset.ariyaBusy='1';">
        @csrf
        <input type="hidden" name="price_id" value="{{ $ariyaVariant->id }}">
        <div class="row align-items-end">
          <div class="col-lg-2 col-md-4 mb-1">
            <strong>تنوع #{{ $ariyaVariant->id }}</strong>
            <div class="small text-muted">{{ $ariyaVariant->getAttributesValue() ?: 'تنوع اصلی محصول' }}</div>
            <div class="small">قیمت پایه: {{ number_format($ariyaRegular) }} تومان</div>
          </div>
          <div class="col-lg-3 col-md-4 mb-1">
            <label>نوع تخفیف</label>
            <select name="mode" class="form-control ariya-dd-mode" required>
              <option value="percent" {{ $ariyaMode==='percent'?'selected':'' }}>درصد تخفیف</option>
              <option value="fixed_amount" {{ $ariyaMode==='fixed_amount'?'selected':'' }}>مبلغ ثابت تخفیف (تومان)</option>
              <option value="fixed_final" {{ $ariyaMode==='fixed_final'?'selected':'' }}>قیمت نهایی ثابت (تومان)</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-4 mb-1">
            <label class="ariya-dd-value-label">{{ $ariyaMode==='percent'?'درصد تخفیف':($ariyaMode==='fixed_amount'?'مبلغ تخفیف (تومان)':'قیمت نهایی (تومان)') }}</label>
            <input type="number" name="value" class="form-control ariya-dd-value" min="0" max="999999999" step="1" required value="{{ $ariyaValue }}">
          </div>
          <div class="col-lg-3 col-md-6 mb-1">
            <label>پایان تخفیف (شمسی؛ اختیاری)</label>
            <input type="text" name="discount_expire_at" class="form-control ariya-dd-jalali" dir="ltr" maxlength="24" placeholder="۱۴۰۵/۰۸/۳۰ ۲۳:۵۹" value="{{ $ariyaExpiryValue }}">
          </div>
          <div class="col-lg-2 col-md-6 mb-1">
            <button type="submit" class="btn btn-primary btn-block">ثبت تخفیف</button>
          </div>
        </div>
        <div class="small mb-1 ariya-dd-preview" aria-live="polite"></div>
        <div class="small text-muted">قیمت فروش فعلی: {{ number_format($ariyaVariant->salePrice()) }} تومان{{ $ariyaExpires?'؛ انقضا: '.$ariyaExpiryValue:'؛ بدون تاریخ پایان' }}.</div>
      </form>
    @empty
      <div class="alert alert-warning">برای این محصول تنوع قیمت فعال وجود ندارد.</div>
    @endforelse
    @if($ariyaDirectVariants->count() > 1)
        </div>
      </details>
    @endif
    <p class="small text-muted mb-0">کمپین گروهی/کنترل فروش ممکن است بر تخفیف اختصاصی اولویت داشته باشد. قیمت نهایی زمان ثبت سفارش در فاکتور ثبت می‌شود.</p>
  </div>
</section>
<script>
(function(){
  var form=document.querySelector('#ariya-direct-discount-editor .ariya-dd-apply-all');
  if(!form)return;
  var mode=form.querySelector('.ariya-dd-bulk-mode'),input=form.querySelector('.ariya-dd-bulk-value'),label=form.querySelector('.ariya-dd-bulk-label'),preview=form.querySelector('.ariya-dd-bulk-preview'),submit=form.querySelector('.ariya-dd-bulk-submit');
  var min=Number(form.getAttribute('data-min-regular')),max=Number(form.getAttribute('data-max-regular'));
  function render(){
    var m=mode.value,v=Number(input.value),valid=input.value.trim()!=='' && Number.isSafeInteger(v)&&v>=0 && min>0;
    label.textContent=m==='percent'?'درصد تخفیف':m==='fixed_amount'?'مبلغ ثابت تخفیف (تومان)':'قیمت نهایی ثابت (تومان)';
    input.max=m==='percent'?'99':'999999999';
    input.min=m==='fixed_final'?'1':'0';
    valid=valid && (m==='percent'?v<=99:(m==='fixed_amount'?(v===0||v<min):(v>=1&&v<min)));
    submit.disabled=!valid;
    if(!valid){preview.textContent='این مقدار برای تمام تنوع‌ها معتبر نیست؛ مقدار را با کمترین قیمت پایه مقایسه کنید.';preview.style.color='#b91c1c';return;}
    var lo=m==='percent'?Math.round(min*(100-v)/100):(m==='fixed_amount'?min-v:v);
    var hi=m==='percent'?Math.round(max*(100-v)/100):(m==='fixed_amount'?max-v:v);
    preview.style.color='';
    preview.textContent='پیش‌نمایش قیمت نهایی تنوع‌ها: از '+lo.toLocaleString('fa-IR')+' تا '+hi.toLocaleString('fa-IR')+' تومان. این ثبت، تخفیف اختصاصی قبلی تمام تنوع‌ها را جایگزین می‌کند.';
  }
  mode.addEventListener('change',function(){input.value=mode.value==='fixed_final'?'':'0';render();});
  input.addEventListener('input',render);render();
})();
</script>
<script>
(function () {
  var forms=document.querySelectorAll('#ariya-direct-discount-editor .ariya-dd-three-form');
  for(var i=0;i<forms.length;i++)(function(form){
    var select=form.querySelector('.ariya-dd-mode'),input=form.querySelector('.ariya-dd-value'),label=form.querySelector('.ariya-dd-value-label'),preview=form.querySelector('.ariya-dd-preview');
    var regular=Number(form.getAttribute('data-regular-toman'))||0;
    function show(){
      var mode=select.value,value=Number(input.value),valid=Number.isSafeInteger(value)&&value>=0;
      label.textContent=mode==='percent'?'درصد تخفیف':mode==='fixed_amount'?'مبلغ تخفیف (تومان)':'قیمت نهایی (تومان)';
      input.max=mode==='percent'?'99':'999999999';
      input.min=mode==='fixed_final'?'1':'0';
      if(!valid || (mode==='percent'&&value>99) || (mode==='fixed_amount'&&value>=regular&&value!==0) || (mode==='fixed_final'&&(value<1||value>=regular))){
        preview.textContent='مقدار نامعتبر است؛ مبلغ باید از قیمت پایه کمتر باشد.';return;
      }
      var final=mode==='percent'?Math.round(regular*(100-value)/100):(mode==='fixed_amount'?regular-value:value);
      var amount=regular-final;
      preview.textContent='پیش‌نمایش: قیمت نهایی '+final.toLocaleString('fa-IR')+' تومان | مبلغ تخفیف '+amount.toLocaleString('fa-IR')+' تومان';
    }
    select.addEventListener('change',function(){input.value=select.value==='fixed_final'?'': '0';show();});
    input.addEventListener('input',show);
    show();
  })(forms[i]);
})();
</script>
@endif
@endcan

{{-- ARIYA_DIRECT_JALALI_DATEPICKER_V1: scripts after the plugin-scripts stack. --}}
@push('scripts')
<script>
(function ($) {
  'use strict';
  if (!$) return;
  $(function () {
    var $fields = $('#ariya-direct-discount-editor input.ariya-dd-jalali');
    if (!$fields.length) return;
    if (!$.fn.pDatepicker) {
      console.warn('Ariya: Persian date-time picker could not load; manual entry remains possible.');
      return;
    }
    $fields.each(function () {
      var $field = $(this);
      if ($field.data('ariyaJalaliInitialized')) return;
      $field.pDatepicker({
        format: 'YYYY/MM/DD HH:mm',
        timePicker: {
          enabled: true,
          meridian: { enabled: false },
          second: { enabled: false }
        },
        toolbox: { calendarSwitch: { enabled: false } },
        initialValue: false,
        autoClose: false
      });
      $field.data('ariyaJalaliInitialized', true);
    });
  });
})(window.jQuery);
</script>
@endpush

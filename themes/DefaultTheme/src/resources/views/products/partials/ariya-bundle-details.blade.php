{{-- ARIYA_BUNDLE_PDP_DETAIL_V1: bundle recipe belongs only on the individual product page. --}}
@if((bool) ($product->is_bundle ?? false))
    @php
        $ariyaBundleRows = \App\Support\BundleContentsPresenter::rows(null, $product->bundle_contents ?? null);
        $ariyaBundleItemCount = array_sum(array_column($ariyaBundleRows, 'quantity'));
    @endphp
    @if(count($ariyaBundleRows))
        @once
            @push('styles')
                <style id="ariya-bundle-pdp-detail-v1-css">
                    .ariya-bundle-detail-panel{direction:rtl;text-align:right;width:100%;margin:20px 0 10px;padding:22px;border-radius:18px;border:1px solid #dbeafe;background:linear-gradient(120deg,#f8fbff,#fff 70%);box-sizing:border-box;box-shadow:0 8px 24px rgba(12,64,112,.04)}
                    .ariya-bundle-detail-header{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:15px}
                    .ariya-bundle-detail-title{display:flex;align-items:center;gap:10px;margin:0;color:#14365c;font-size:18px;font-weight:800;line-height:1.7}
                    .ariya-bundle-detail-title i{display:inline-grid;place-items:center;width:38px;height:38px;flex:none;border-radius:12px;background:#e0efff;color:#0877c9;font-size:21px}
                    .ariya-bundle-detail-intro{margin:3px 0 0;color:#64748b;font-size:12px;line-height:1.8}
                    .ariya-bundle-detail-counter{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
                    .ariya-bundle-detail-counter span{padding:6px 10px;border-radius:100px;background:#e8f3ff;color:#14558a;font-weight:750;font-size:12px}
                    .ariya-bundle-detail-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:10px;max-height:410px;overflow-y:auto;padding:3px 2px 8px;overscroll-behavior:contain}
                    .ariya-bundle-detail-entry{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;min-width:0;padding:12px 14px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-sizing:border-box}
                    .ariya-bundle-detail-description{min-width:0;flex:1;overflow-wrap:anywhere}
                    .ariya-bundle-detail-name{display:block;font-weight:750;color:#26394c;font-size:13px;line-height:1.75}
                    .ariya-bundle-detail-variant{display:block;font-size:12px;line-height:1.7;color:#64748b;margin-top:2px}
                    .ariya-bundle-detail-quantity{white-space:nowrap;flex:none;border-radius:8px;background:#e8f3ff;color:#0867ac;padding:6px 9px;font-weight:800;font-size:12px}
                    .ariya-bundle-detail-note{margin:12px 0 0;padding-top:12px;border-top:1px dashed #cbd5e1;color:#64748b;font-size:12px;line-height:1.8}
                    @media(max-width:575px){.ariya-bundle-detail-panel{padding:14px;border-radius:14px}.ariya-bundle-detail-title{font-size:16px}.ariya-bundle-detail-grid{grid-template-columns:minmax(0,1fr);max-height:400px}}
                </style>
            @endpush
        @endonce
        <section class="col-12 ariya-bundle-detail-panel" aria-labelledby="ariya-bundle-detail-title-{{ $product->id }}">
            <div class="ariya-bundle-detail-header">
                <div>
                    <h2 class="ariya-bundle-detail-title" id="ariya-bundle-detail-title-{{ $product->id }}"><i class="mdi mdi-package-variant-closed" aria-hidden="true"></i>محتویات این پک</h2>
                    <p class="ariya-bundle-detail-intro">مدل‌ها و تعداد هر کالا در یک بسته، پیش از خرید</p>
                </div>
                <div class="ariya-bundle-detail-counter">
                    <span>{{ number_format(count($ariyaBundleRows)) }} ردیف کالا</span>
                    <span>{{ number_format($ariyaBundleItemCount) }} عدد در هر پک</span>
                </div>
            </div>
            <div class="ariya-bundle-detail-grid" role="list" aria-label="فهرست کالاهای موجود در پک">
                @foreach($ariyaBundleRows as $ariyaBundleRow)
                    <div class="ariya-bundle-detail-entry" role="listitem">
                        <div class="ariya-bundle-detail-description">
                            <strong class="ariya-bundle-detail-name">{{ $ariyaBundleRow['name'] }}</strong>
                            @if($ariyaBundleRow['variant'] !== '' && $ariyaBundleRow['variant'] !== $ariyaBundleRow['name'])
                                <span class="ariya-bundle-detail-variant">{{ $ariyaBundleRow['variant'] }}</span>
                            @endif
                        </div>
                        <span class="ariya-bundle-detail-quantity">× {{ number_format($ariyaBundleRow['quantity']) }}</span>
                    </div>
                @endforeach
            </div>
            <p class="ariya-bundle-detail-note"><i class="mdi mdi-information-outline" aria-hidden="true"></i> مقادیر فوق مربوط به یک پک است؛ قیمت، موجودی و ثبت سفارش در بخش خرید همین محصول مدیریت می‌شود.</p>
        </section>
    @endif
@endif

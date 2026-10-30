@php
	$inv = $inventoryInvoice ?? $inventoryRevision ?? [];
$currency =  trim(trans('front::messages.currency.suffix'));
$isSitePreview = ($inv['source'] ?? '') === 'site_order';
$items = collect($inv['current_items'] ?? []);
	$removedItems = collect($inv['removed_items'] ?? []);
	$payments = collect($inv['payments'] ?? []);
	$revision = (array) ($inv['revision'] ?? []);
	$fmt = fn ($value) => number_format((int) $value);
    $money = fn ($value) => number_format((int) $value / 10);
	$invDate = function ($value) {
		if (!$value) return '—';
		try {
			return jdate(\Illuminate\Support\Carbon::parse($value))->format('Y/m/d H:i');
		} catch (\Throwable $e) {
			return (string) $value;
		}
	};
	$status = (string) ($inv['status'] ?? '');
	$statusTone = match ($status) {
		'shipped', 'ready_to_ship' => 'success',
		'pending_finance_reapproval', 'returned_to_sales_after_collection' => 'warning',
		'not_shipped' => 'danger',
		default => 'info',
	};
	$paymentTone = match ((string)($inv['payment_status'] ?? '')) {
		'paid' => 'success',
		'overpaid' => 'violet',
		'partial' => 'warning',
		default => 'danger',
	};
	$revisionSource = $revision['source_label'] ?? (($revision['source'] ?? '') === 'invoice_edit' ? 'ویرایش فاکتور' : 'انبار / جمع‌آوری');
	$revisionReason = $revision['reason_label'] ?? ($revision['reason_type'] ?? '—');
    $timeline = $isSitePreview ? [
        ['label' => 'ثبت سفارش سایت', 'date' => $inv['created_at'] ?? null, 'done' => true],
        ['label' => 'پرداخت', 'date' => null, 'done' => ($inv['payment_status'] ?? '') === 'paid'],
        ['label' => 'ارسال', 'date' => null, 'done' => $status === 'shipped'],
    ] : [
        ['label' => 'ثبت فاکتور', 'date' => $inv['document_date'] ?? $inv['created_at'] ?? null, 'done' => true],
        ['label' => 'دریافت انبار', 'date' => $inv['warehouse_received_at'] ?? null, 'done' => !empty($inv['warehouse_received_at']) || in_array($status, ['warehouse_received','collecting','pending_finance_reapproval','ready_to_ship','shipped'], true)],
        ['label' => 'جمع‌آوری', 'date' => $inv['collection_started_at'] ?? $inv['collected_at'] ?? null, 'done' => !empty($inv['collection_started_at']) || in_array($status, ['collecting','pending_finance_reapproval','ready_to_ship','shipped'], true)],
        ['label' => 'تأیید مالی / آماده ارسال', 'date' => $status === 'ready_to_ship' ? ($inv['status_changed_at'] ?? null) : null, 'done' => in_array($status, ['ready_to_ship','shipped'], true)],
        ['label' => 'ارسال', 'date' => $inv['shipped_at'] ?? null, 'done' => $status === 'shipped'],
    ];
@endphp

<style>
	.inv-order-shell{direction:rtl;background:#fff;border:1px solid #e7edf5;border-radius:22px;box-shadow:0 14px 38px rgba(15,23,42,.06);overflow:hidden;margin-bottom:24px;color:#172033}
	.inv-order-head{padding:20px 22px 16px;background:linear-gradient(180deg,#fbfdff 0%,#f7faff 100%);border-bottom:1px solid #e9eef5;display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
	.inv-order-eyebrow{display:flex;align-items:center;gap:7px;color:#6b7a90;font-size:11px;font-weight:700;margin-bottom:5px}.inv-order-eyebrow:before{content:"";width:7px;height:7px;border-radius:50%;background:#1687d9;box-shadow:0 0 0 4px #e7f4ff}
	.inv-order-title{font-size:19px;font-weight:900;color:#102a43;margin:0 0 6px}.inv-order-subtitle{font-size:11px;color:#7b8799;line-height:1.9}
	.inv-badges{display:flex;gap:7px;flex-wrap:wrap}.inv-badge{display:inline-flex;align-items:center;gap:5px;padding:6px 10px;border-radius:999px;font-size:10px;font-weight:900;border:1px solid transparent;white-space:nowrap}
	.inv-badge.success{background:#edf9f1;color:#177245;border-color:#ccebd8}.inv-badge.info{background:#eef7ff;color:#17639a;border-color:#d1e9fb}.inv-badge.warning{background:#fff8e8;color:#9a6200;border-color:#f6dfab}.inv-badge.danger{background:#fff1f2;color:#a8323d;border-color:#f2cdd1}.inv-badge.violet{background:#f5f0ff;color:#6842a8;border-color:#ded1f6}.inv-badge.neutral{background:#f4f6f8;color:#5f6b7a;border-color:#e3e8ee}
	.inv-order-body{padding:18px 20px 22px}.inv-section{margin-top:18px}.inv-section:first-child{margin-top:0}.inv-section-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}.inv-section-title{font-size:13px;font-weight:900;color:#203247;margin:0}.inv-section-hint{font-size:10px;color:#8a96a8}
	.inv-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px}.inv-stat{border:1px solid #e8edf3;border-radius:14px;padding:11px 12px;background:#fff;min-height:70px}.inv-stat.primary{background:#f3f9ff;border-color:#d9ecfc}.inv-stat.success{background:#f4fbf6;border-color:#d8efdf}.inv-stat.warning{background:#fffaf0;border-color:#f4e4bc}.inv-stat.danger{background:#fff6f6;border-color:#f2dada}.inv-stat small{display:block;font-size:9px;color:#8994a4;margin-bottom:5px}.inv-stat strong{display:block;font-size:13px;font-weight:900;color:#1c2a3b;white-space:nowrap}.inv-stat .sub{display:block;margin-top:3px;color:#9aa4b1;font-size:9px}
	.inv-meta-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}.inv-meta{background:#f8fafc;border:1px solid #edf1f5;border-radius:12px;padding:9px 11px}.inv-meta span{display:block;color:#8a96a8;font-size:9px;margin-bottom:4px}.inv-meta b{display:block;color:#334155;font-size:11px;font-weight:800;line-height:1.7;overflow-wrap:anywhere}
	.inv-timeline{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:0;background:#fafcff;border:1px solid #e8eef5;border-radius:14px;padding:13px 10px}.inv-timeline.site-preview{grid-template-columns:repeat(3,minmax(0,1fr))}.inv-step{position:relative;text-align:center;padding:0 6px}.inv-step:not(:last-child):after{content:"";position:absolute;top:9px;left:-50%;width:100%;height:2px;background:#e1e7ee;z-index:0}.inv-step.done:not(:last-child):after{background:#b9dfca}.inv-step-dot{position:relative;z-index:1;width:18px;height:18px;margin:0 auto 7px;border-radius:50%;background:#e9eef4;border:4px solid #fff;box-shadow:0 0 0 1px #dce3eb}.inv-step.done .inv-step-dot{background:#42a86b;box-shadow:0 0 0 1px #bfe6ce}.inv-step b{display:block;font-size:9px;color:#536174}.inv-step small{display:block;font-size:8px;color:#98a2b1;margin-top:3px;white-space:nowrap}
	.inv-revision-box{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;background:#fffaf2;border:1px solid #f3e0b5;border-radius:14px;padding:12px 14px}.inv-revision-main{min-width:0}.inv-revision-main strong{display:block;color:#7c5208;font-size:11px;margin-bottom:4px}.inv-revision-main p{margin:0;color:#806c49;font-size:10px;line-height:1.9}.inv-revision-diff{text-align:left;white-space:nowrap}.inv-revision-diff small{display:block;color:#a28b61;font-size:8px}.inv-revision-diff b{display:block;color:#6d4a0c;font-size:12px;margin-top:3px}
	.inv-items-wrap{border:1px solid #e7edf4;border-radius:16px;overflow:hidden;background:#fff}.inv-items-table{width:100%;min-width:1120px;margin:0!important}.inv-items-table thead th{background:#f7f9fc!important;color:#607087!important;font-size:9px!important;font-weight:900!important;padding:10px 8px!important;border-bottom:1px solid #e7edf4!important;white-space:nowrap;text-align:center!important}.inv-items-table tbody td{font-size:10px!important;padding:10px 8px!important;vertical-align:middle!important;border-top:1px solid #f0f3f7!important;text-align:center!important;color:#344054}.inv-items-table tbody tr:first-child td{border-top:0!important}.inv-items-table tr.row-added td{background:#f6fcf8}.inv-items-table tr.row-changed td{background:#fffbf2}.inv-items-table tr.row-removed td{background:#fafbfc;color:#98a2b3}.inv-product{display:flex;align-items:center;gap:9px;text-align:right;min-width:230px}.inv-product-img{width:46px;height:46px;border-radius:11px;object-fit:contain;background:#f7f8fa;border:1px solid #edf0f3;flex:0 0 46px}.inv-product-info{min-width:0}.inv-product-name{display:block;color:#24364b;font-weight:900;font-size:10px;line-height:1.7}.inv-product-name:hover{color:#1479bd}.inv-product-model{display:block;color:#8a96a8;font-size:9px;line-height:1.7;margin-top:2px}.row-removed .inv-product-name,.row-removed .inv-product-model{text-decoration:line-through;color:#98a2b3}.inv-code{direction:ltr;font-family:monospace;font-size:9px;color:#667085}.inv-money{white-space:nowrap;font-weight:800;color:#344054}.inv-money small{display:block;font-size:8px;color:#98a2b3;font-weight:500;margin-top:2px}.inv-old{text-decoration:line-through;color:#a8b0bc;font-weight:500}.inv-arrow{display:inline-block;margin:0 4px;color:#98a2b3}.inv-return{font-weight:900;color:#aa5d17}.inv-zero{color:#b0b8c3}.inv-row-badge{display:inline-flex;border-radius:999px;padding:4px 7px;font-size:8px;font-weight:900;white-space:nowrap}.inv-row-badge.added{background:#eaf8ef;color:#237347}.inv-row-badge.changed{background:#fff4d8;color:#8a5a00}.inv-row-badge.removed{background:#ffedef;color:#a53742}.inv-row-badge.normal{background:#f1f4f7;color:#667085}.inv-row-badge.unavailable{background:#fff1f2;color:#a8323d}.inv-items-table tr.row-unavailable td{background:#fffafa}
	.inv-mobile-items{display:none}.inv-mobile-card{border:1px solid #e7edf4;border-radius:14px;padding:12px;background:#fff;margin-bottom:9px}.inv-mobile-card.row-added{background:#f6fcf8}.inv-mobile-card.row-changed{background:#fffbf2}.inv-mobile-card.row-removed{background:#fafbfc}.inv-mobile-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:9px}.inv-mobile-values{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}.inv-mobile-value{border:1px solid #eef1f4;border-radius:10px;padding:7px 8px;background:rgba(255,255,255,.72)}.inv-mobile-value small{display:block;color:#98a2b3;font-size:8px;margin-bottom:2px}.inv-mobile-value b{font-size:10px;color:#344054}
	.inv-payments{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.inv-payment{border:1px solid #e8edf3;border-radius:12px;padding:10px 12px;background:#fafcff;display:flex;justify-content:space-between;align-items:center;gap:10px}.inv-payment strong{font-size:10px;color:#344054}.inv-payment small{display:block;color:#8f9aaa;font-size:8px;margin-top:3px}.inv-payment-amount{font-size:11px;font-weight:900;color:#177245;white-space:nowrap}.inv-payment-amount.failed{color:#a8323d}
	.inv-source-note{margin-top:12px;padding:9px 11px;border-radius:10px;background:#f7f9fb;color:#7c8796;font-size:9px;line-height:1.9}.inv-source-note b{color:#516072}
	@media(max-width:991px){.inv-timeline.site-preview{grid-template-columns:repeat(3,minmax(150px,1fr))}.inv-summary-grid,.inv-meta-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.inv-timeline{overflow-x:auto;grid-template-columns:repeat(5,150px);justify-content:start}.inv-payments{grid-template-columns:1fr}}
	@media(max-width:767px){.inv-order-head,.inv-order-body{padding-left:12px;padding-right:12px}.inv-order-title{font-size:16px}.inv-summary-grid,.inv-meta-grid{grid-template-columns:1fr 1fr}.inv-items-desktop{display:none}.inv-mobile-items{display:block}.inv-revision-box{flex-direction:column}.inv-revision-diff{text-align:right}.inv-stat strong{font-size:12px}}
	@media(max-width:420px){.inv-summary-grid,.inv-meta-grid{grid-template-columns:1fr}.inv-mobile-values{grid-template-columns:1fr 1fr}}
</style>

<div class="inv-order-shell">
	<div class="inv-order-head">
		<div>
			<h3 class="inv-order-eyebrow inv-order-title">{{ $isSitePreview ? 'سفارش سایت #' . $order->id : 'فاکتور ' . ($inv['invoice_uuid'] ?? '—') }}</h3>
			<div class="inv-order-subtitle">
				سفارش سایت #{{ $order->id }}
				@if($isSitePreview) · ثبت {{ $invDate($inv['created_at'] ?? null) }} @elseif(!empty($inv['document_date'])) · تاریخ فاکتور {{ $invDate($inv['document_date']) }} @endif
				· {{ number_format((int)($inv['items_count'] ?? $items->count())) }} ردیف / {{ number_format((int)($inv['total_quantity'] ?? $items->sum('quantity'))) }} عدد
			</div>
		</div>
		<div class="inv-badges">
			<span class="inv-badge {{ $statusTone }}">{{ $inv['status_label'] ?? 'وضعیت نامشخص' }}</span>
			<span class="inv-badge {{ $paymentTone }}">{{ $inv['payment_status_label'] ?? 'وضعیت پرداخت نامشخص' }}</span>
			@if(!empty($revision['number']))
				<span class="inv-badge neutral">{{ number_format((int)($inv['revision_count'] ?? 1)) }} اصلاح ثبت‌شده</span>
			@endif
		</div>
	</div>

    <div class="inv-order-body">
        @if($isSitePreview)
            <div class="inv-source-note" role="status">
                <b>اطلاعات ثبت‌شده در سایت:</b> فاکتور انبار هنوز دریافت نشده است. مبالغ و اقلام سفارش سایت نمایش داده می‌شوند؛ به‌محض ثبت فاکتور انبار، همین قالب با اطلاعات به‌روز انبار نمایش داده خواهد شد.
            </div>
        @endif
		<div class="inv-section">
			<div class="inv-section-head"><h4 class="inv-section-title">خلاصه مالی فاکتور</h4></div>
			<div class="inv-summary-grid">
				<div class="inv-stat"><small>جمع ناخالص اقلام</small><strong>{{ $money($inv['subtotal'] ?? 0) }} {{ $currency }}</strong></div>
				<div class="inv-stat"><small>جمع تخفیف</small><strong>{{ $money($inv['discount_amount'] ?? 0) }} {{ $currency }}</strong><span class="sub">محصول: {{ $money($inv['product_discount_amount'] ?? 0) }} · فاکتور: {{ $money($inv['invoice_discount_amount'] ?? 0) }}</span></div>
				<div class="inv-stat"><small>هزینه ارسال</small><strong>{{ $money($inv['shipping_price'] ?? 0) }} {{ $currency }}</strong><span class="sub">{{ $inv['shipping_method_name'] ?? 'روش ارسال ثبت نشده' }}</span></div>
				<div class="inv-stat primary"><small>مبلغ نهایی فاکتور</small><strong>{{ $money($inv['total'] ?? 0) }} {{ $currency }}</strong></div>
				<div class="inv-stat success"><small>پرداخت‌شده</small><strong>{{ $money($inv['paid_amount'] ?? 0) }} {{ $currency }}</strong></div>
				<div class="inv-stat {{ (int)($inv['remaining_amount'] ?? 0) > 0 ? 'danger' : 'success' }}"><small>مانده قابل پرداخت</small><strong>{{ $money($inv['remaining_amount'] ?? 0) }} {{ $currency }}</strong></div>
				<div class="inv-stat {{ (int)($inv['overpaid_amount'] ?? 0) > 0 ? 'warning' : '' }}"><small>اضافه‌پرداخت / اعتبار</small><strong>{{ $money($inv['overpaid_amount'] ?? 0) }} {{ $currency }}</strong></div>
				<div class="inv-stat"><small>مرجوعی ثبت‌شده</small><strong>{{ $fmt($inv['returned_quantity'] ?? 0) }} عدد</strong><span class="sub">{{ $isSitePreview ? 'تعداد ثبت‌شده' : 'باقی‌مانده فعلی' }}: {{ $fmt($isSitePreview ? ($inv['total_quantity'] ?? 0) : ($inv['remaining_quantity'] ?? 0)) }} عدد</span></div>
			</div>
		</div>

		<div class="inv-section">
			<div class="inv-section-head"><h4 class="inv-section-title">اطلاعات و وضعیت عملیات</h4></div>
			<div class="inv-meta-grid">
				<div class="inv-meta"><span>{{ $isSitePreview ? 'شماره سفارش سایت' : 'شماره فاکتور' }}</span><b>{{ $isSitePreview ? $order->id : ($inv['invoice_uuid'] ?? '—') }}</b></div>
				<div class="inv-meta"><span>وضعیت جاری</span><b>{{ $inv['status_label'] ?? '—' }}</b></div>
				<div class="inv-meta"><span>روش ارسال</span><b>{{ $inv['shipping_method_name'] ?? '—' }}</b></div>
				<div class="inv-meta"><span>{{ $isSitePreview ? 'آخرین به‌روزرسانی سفارش' : 'آخرین بروزرسانی اقلام' }}</span><b>{{ $invDate($inv['items_updated_at'] ?? $inv['updated_at'] ?? null) }}</b></div>
				<div class="inv-meta"><span>به نام</span><b>{{ $inv['customer_name'] ?? '—' }}</b></div>
				<div class="inv-meta"><span>موبایل</span><b dir="ltr">{{ $inv['customer_mobile'] ?? '—' }}</b></div>
				@unless($isSitePreview)<div class="inv-meta"><span>شروع جمع‌آوری</span><b>{{ $invDate($inv['collection_started_at'] ?? null) }}</b></div>@endunless
				@unless($isSitePreview)<div class="inv-meta"><span>ارسال نهایی</span><b>{{ $invDate($inv['shipped_at'] ?? null) }}</b></div>@endunless
			</div>
		</div>

		<div class="inv-section">
			<div class="inv-section-head"><h4 class="inv-section-title">روند آماده‌سازی سفارش</h4></div>
			<div class="inv-timeline {{ $isSitePreview ? 'site-preview' : '' }}">
				@foreach($timeline as $step)
					<div class="inv-step {{ $step['done'] ? 'done' : '' }}">
						<div class="inv-step-dot"></div>
						<b>{{ $step['label'] }}</b>
						<small>{{ $invDate($step['date']) }}</small>
					</div>
				@endforeach
			</div>
		</div>

		@if(!empty($revision['number']))
			<div class="inv-section">
				<div class="inv-revision-box">
					<div class="inv-revision-main">
						<strong>آخرین اصلاح فاکتور: #{{ number_format((int)$revision['number']) }} · {{ $revisionReason }}</strong>
						<p>
							مسیر تغییر: {{ $revisionSource }}
							@if(!empty($revision['changed_by_name'])) · ثبت‌کننده: {{ $revision['changed_by_name'] }} @endif
							@if(!empty($revision['changed_at'])) · {{ $invDate($revision['changed_at']) }} @endif
							@if(!empty($revision['reason_note']))<br>{{ $revision['reason_note'] }}@endif
						</p>
					</div>
					<div class="inv-revision-diff">
						<small>تغییر مبلغ نسبت به قبل</small>
						<b>{{ $money($inv['difference'] ?? 0) }} {{ $currency }}</b>
						<small>{{ $money($inv['old_total'] ?? 0) }} ← {{ $money($inv['total'] ?? 0) }}</small>
					</div>
				</div>
			</div>
		@endif

		<div class="inv-section">
			<div class="inv-section-head"><h4 class="inv-section-title">اقلام نهایی فاکتور</h4></div>

			<div class="inv-items-wrap inv-items-desktop table-responsive">
				<table class="table inv-items-table">
					<thead>
					<tr><th>وضعیت</th><th style="text-align:right!important">کالا / مدل</th><th>کد / SKU</th><th>تعداد</th><th>مرجوعی</th><th>{{ $isSitePreview ? 'تعداد پس از اصلاح سایت' : 'قابل تحویل' }}</th><th>قیمت واحد</th><th>تخفیف ردیف</th><th>جمع ردیف</th></tr>
					</thead>
					<tbody>
					@foreach($items as $item)
						@php
							$change = (array)($item['latest_change'] ?? []);
							$changeType = $item['change_type'] ?? null;
							$rowClass = $changeType === 'added' ? 'row-added' : ($changeType === 'multiple_changes' ? 'row-changed' : ($changeType === 'unavailable' ? 'row-unavailable' : ''));
							$siteProduct = $item['site_product'] ?? null;
							$productImage = $siteProduct?->image ?: '/empty.jpg';
						@endphp
						<tr class="{{ $rowClass }}">
							<td>
								@if($changeType === 'added')<span class="inv-row-badge added">اضافه‌شده</span>
								@elseif($changeType === 'multiple_changes')<span class="inv-row-badge changed">ویرایش‌شده</span>
                                @elseif($changeType === 'unavailable')<span class="inv-row-badge unavailable">غیرقابل ارسال</span>
								@else<span class="inv-row-badge normal">فعال</span>@endif
							</td>
							<td style="text-align:right!important">
								<div class="inv-product">
									@if($siteProduct)<a href="{{ route('front.products.show', ['product' => $siteProduct]) }}" target="_blank"><img class="inv-product-img" src="{{ $productImage }}" alt=""></a>@else<img class="inv-product-img" src="{{ $productImage }}" alt="">@endif
									<div class="inv-product-info">
										@if($siteProduct)<a class="inv-product-name" href="{{ route('front.products.show', ['product' => $siteProduct]) }}" target="_blank">{{ $item['product_name'] ?: $siteProduct->title }}</a>@else<span class="inv-product-name">{{ $item['product_name'] ?? '—' }}</span>@endif
										<span class="inv-product-model">{{ $item['variant_name'] ?? '—' }}</span>
                                        @include('front::partials.bundle-contents', ['bundleSnapshot' => $item['bundle_components_snapshot'] ?? null, 'bundleProduct' => $siteProduct, 'bundleQuantity' => $item['quantity'] ?? 1])
                                        @if($isSitePreview && $siteProduct && $siteProduct->isDownload() && ($item['site_price'] ?? null)?->isDownloadable())
                                            <a class="inv-row-badge added" href="{{ $item['site_price']->downloadLink() }}">دانلود فایل</a>
                                        @endif
                                        @if($isSitePreview && !empty($item['site_fulfillment_message']))
                                            <span class="inv-product-model">{{ $item['site_fulfillment_message'] }}</span>
                                        @endif
									</div>
								</div>
							</td>
							<td><span class="inv-code">{{ $item['sku'] ?? '—' }}</span></td>
							<td>
								@if($changeType === 'added')<span class="inv-old">۰</span><span class="inv-arrow">←</span><b>{{ $fmt($item['quantity'] ?? 0) }}</b>
								@elseif($changeType === 'multiple_changes' && isset($change['old_quantity']) && (int)$change['old_quantity'] !== (int)($item['quantity'] ?? 0))<span class="inv-old">{{ $fmt($change['old_quantity']) }}</span><span class="inv-arrow">←</span><b>{{ $fmt($item['quantity'] ?? 0) }}</b>
								@else<b>{{ $fmt($item['quantity'] ?? 0) }}</b>@endif
							</td>
							<td class="{{ (int)($item['returned_quantity'] ?? 0) > 0 ? 'inv-return' : 'inv-zero' }}">{{ $fmt($item['returned_quantity'] ?? 0) }}</td>
							<td><b>{{ $fmt($item['remaining_quantity'] ?? $item['quantity'] ?? 0) }}</b></td>
							<td class="inv-money">
								@if($changeType === 'multiple_changes' && isset($change['old_price']) && (int)$change['old_price'] !== (int)($item['price'] ?? 0))<span class="inv-old">{{ $money($change['old_price']) }}</span><span class="inv-arrow">←</span>@endif
								{{ $money($item['price'] ?? 0) }}<small>{{ $currency }}</small>
							</td>
							<td class="inv-money">{{ $money($item['discount'] ?? 0) }}<small>{{ $currency }}</small></td>
							<td class="inv-money">{{ $money($item['line_total'] ?? 0) }}<small>{{ $currency }}</small></td>
						</tr>
					@endforeach
					@foreach($removedItems as $removed)
						@php $siteProduct = $removed['site_product'] ?? null; $productImage = $siteProduct?->image ?: '/empty.jpg'; @endphp
						<tr class="row-removed">
							<td><span class="inv-row-badge removed">حذف‌شده</span></td>
							<td style="text-align:right!important"><div class="inv-product"><img class="inv-product-img" src="{{ $productImage }}" alt=""><div class="inv-product-info"><span class="inv-product-name">{{ $removed['product_name'] ?? '—' }}</span><span class="inv-product-model">{{ $removed['variant_name'] ?? '—' }}</span></div></div></td>
							<td><span class="inv-code">{{ $removed['sku'] ?? '—' }}</span></td>
							<td><span class="inv-old">{{ $fmt($removed['old_quantity'] ?? 0) }}</span><span class="inv-arrow">←</span><b>۰</b></td>
							<td class="inv-zero">—</td><td><b>۰</b></td>
							<td class="inv-money"><span class="inv-old">{{ $money($removed['old_price'] ?? 0) }}</span><small>{{ $currency }}</small></td>
							<td class="inv-money"><span class="inv-old">{{ $money($removed['old_discount'] ?? 0) }}</span><small>{{ $currency }}</small></td>
							<td class="inv-money"><span class="inv-old">{{ $money($removed['old_line_total'] ?? 0) }}</span><span class="inv-arrow">←</span>۰<small>{{ $currency }}</small></td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>

			<div class="inv-mobile-items">
				@foreach($items as $item)
					@php
						$change = (array)($item['latest_change'] ?? []); $changeType = $item['change_type'] ?? null;
						$rowClass = $changeType === 'added' ? 'row-added' : ($changeType === 'multiple_changes' ? 'row-changed' : ($changeType === 'unavailable' ? 'row-unavailable' : ''));
						$siteProduct = $item['site_product'] ?? null; $productImage = $siteProduct?->image ?: '/empty.jpg';
					@endphp
					<div class="inv-mobile-card {{ $rowClass }}">
						<div class="inv-mobile-top"><div class="inv-product"><img class="inv-product-img" src="{{ $productImage }}" alt=""><div class="inv-product-info"><span class="inv-product-name">{{ $item['product_name'] ?? '—' }}</span><span class="inv-product-model">{{ $item['variant_name'] ?? '—' }} · {{ $item['sku'] ?? '—' }}</span>@include('front::partials.bundle-contents', ['bundleSnapshot' => $item['bundle_components_snapshot'] ?? null, 'bundleProduct' => $siteProduct, 'bundleQuantity' => $item['quantity'] ?? 1])
                                        @if($isSitePreview && $siteProduct && $siteProduct->isDownload() && ($item['site_price'] ?? null)?->isDownloadable())
                                            <a class="inv-row-badge added" href="{{ $item['site_price']->downloadLink() }}">دانلود فایل</a>
                                        @endif
                                        @if($isSitePreview && !empty($item['site_fulfillment_message']))
                                            <span class="inv-product-model">{{ $item['site_fulfillment_message'] }}</span>
                                        @endif</div></div>@if($changeType === 'added')<span class="inv-row-badge added">اضافه‌شده</span>@elseif($changeType === 'multiple_changes')<span class="inv-row-badge changed">ویرایش‌شده</span>@elseif($changeType === 'unavailable')<span class="inv-row-badge unavailable">غیرقابل ارسال</span>@else<span class="inv-row-badge normal">فعال</span>@endif</div>
						<div class="inv-mobile-values">
							<div class="inv-mobile-value"><small>تعداد</small><b>{{ $fmt($item['quantity'] ?? 0) }}</b></div>
							<div class="inv-mobile-value"><small>مرجوعی / قابل تحویل</small><b>{{ $fmt($item['returned_quantity'] ?? 0) }} / {{ $fmt($item['remaining_quantity'] ?? 0) }}</b></div>
							<div class="inv-mobile-value"><small>قیمت واحد</small><b>{{ $money($item['price'] ?? 0) }} {{ $currency }}</b></div>
							<div class="inv-mobile-value"><small>جمع ردیف</small><b>{{ $money($item['line_total'] ?? 0) }} {{ $currency }}</b></div>
						</div>
					</div>
				@endforeach
				@foreach($removedItems as $removed)
						<div class="inv-mobile-card row-removed"><div class="inv-mobile-top"><div class="inv-product"><img class="inv-product-img" src="/empty.jpg" alt=""><div class="inv-product-info"><span class="inv-product-name">{{ $removed['product_name'] ?? '—' }}</span><span class="inv-product-model">{{ $removed['variant_name'] ?? '—' }} · {{ $removed['sku'] ?? '—' }}</span></div></div><span class="inv-row-badge removed">حذف‌شده</span></div><div class="inv-mobile-values"><div class="inv-mobile-value"><small>تعداد قبل → فعلی</small><b>{{ $fmt($removed['old_quantity'] ?? 0) }} ← ۰</b></div><div class="inv-mobile-value"><small>قیمت قبلی</small><b>{{ $money($removed['old_price'] ?? 0) }} {{ $currency }}</b></div></div></div>
					@endforeach
			</div>
		</div>

		@if($payments->isNotEmpty())
			<div class="inv-section">
				<div class="inv-section-head"><h4 class="inv-section-title">{{ $isSitePreview ? 'تراکنش‌های سفارش سایت' : 'پرداخت‌های ثبت‌شده روی فاکتور' }}</h4></div>
				<div class="inv-payments">
					@foreach($payments as $payment)
						<div class="inv-payment"><div><strong>{{ $payment['method_label'] ?? 'پرداخت' }}</strong><small>#{{ $payment['id'] ?? '—' }} · {{ $invDate($payment['paid_at'] ?? null) }}@if(!empty($payment['identifier'])) · {{ $payment['identifier'] }}@endif</small></div><div class="inv-payment-amount {{ array_key_exists('is_successful', $payment) && !$payment['is_successful'] ? 'failed' : '' }}">{{ $money($payment['amount'] ?? 0) }} {{ $currency }}</div></div>
					@endforeach
				</div>
			</div>
		@endif
	</div>
</div>

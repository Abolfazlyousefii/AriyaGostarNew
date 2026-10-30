@can('products.update')
@if(\Illuminate\Support\Facades\Schema::hasTable('bulk_discount_items'))
@php
    $ariyaBulkItem = \App\Models\BulkDiscountItem::query()
        ->with('campaign')->where('product_id', $bulkProduct->id)->first();
@endphp
@if($ariyaBulkItem && $ariyaBulkItem->campaign)
<div class="alert alert-info mb-2" role="status">
    <strong>تخفیف گروهی این محصول:</strong> {{ $ariyaBulkItem->campaign->title }}
    ({{ $ariyaBulkItem->campaign->discount_type === 'percent' ? number_format($ariyaBulkItem->campaign->discount_value) . ' درصد' : 'قیمت ثابت ' . number_format($ariyaBulkItem->campaign->discount_value) . ' تومان' }})
    — {{ $ariyaBulkItem->campaign->enabled ? 'فعال / وابسته به زمان‌بندی' : 'کمپین غیرفعال' }}
    <form method="POST" action="{{ route('admin.bulk-discounts.remove-product', $ariyaBulkItem->campaign_id) }}" class="mt-1" onsubmit="return confirm('تخفیف گروهی این محصول برداشته شود؟ تخفیف دستی دست‌نخورده باقی می‌ماند.')">
        @csrf <input type="hidden" name="product_id" value="{{ $bulkProduct->id }}">
        <button type="submit" class="btn btn-sm btn-outline-danger">حذف تخفیف گروهی فقط این محصول</button>
    </form>
</div>
@endif
@endif
@endcan

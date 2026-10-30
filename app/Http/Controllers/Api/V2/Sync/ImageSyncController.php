<?php

namespace App\Http\Controllers\Api\V2\Sync;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageSyncController extends Controller
{
    /**
     * دریافت محصولات دارای تصویر که هنوز به سرور انبار منتقل نشده‌اند.
     */
    public function pending(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:200',
            ],

            'after_id' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $limit = (int)($validated['limit'] ?? 100);
        $afterId = (int)($validated['after_id'] ?? 0);

        $products = Product::query()
            ->where('site_to_inventory_verified', false)
            ->where('id', '>', $afterId)
            ->where('image', '!=', '')
            ->orderBy('id')
            ->limit($limit)->get();

        return response()->json([
            'success' => true,
            'count' => $products->count(),

            'data' => $products
                ->map(static function (Product $product): array {
                    return [
                        'code' => $product->product_code,
                        'image' => $product->image,
                    ];
                })
                ->values(),

        ]);
    }

    /**
     * تأیید محصولاتی که تصویرشان با موفقیت در سرور اول ذخیره شده است.
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],

            'product_ids.*' => [
                'required',
                'distinct',
                'min:1',
            ],
        ]);
        $productIds = collect($validated['product_ids'])
            ->map(static fn($id): int => (int)$id)
            ->unique()
            ->values()
            ->all();

        /*
         * Query Update باعث می‌شود رویدادهای مدل دخالت نکنند
         * و فلگ دوباره صفر نشود.
         */
        $updatedCount = Product::query()
            ->whereIn('product_code', $productIds)
            ->where('site_to_inventory_verified', false)
            ->update([
                'site_to_inventory_verified' => true,
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,

            'data' => [
                'requested_ids' => $productIds,
                'updated_count' => $updatedCount,
            ],
        ]);
    }
}

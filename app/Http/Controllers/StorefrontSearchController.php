<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StorefrontSearchController extends Controller
{
    public function ajax(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'max:150'],
        ]);

        $term = trim((string) $request->input('q'));

        if ($term === '') {
            return response()->json([])
                ->header('Cache-Control', 'private, no-store, max-age=0')
                ->header('Vary', 'Cookie');
        }

        // ARIYA_GUEST_SEARCH_PRICE_GUARD_V1: only authenticated accounts may
        // receive price values. Hiding a DOM element is not an access control.
        $canViewPrice = auth()->check();
        $relations = $canViewPrice
            ? ['category:id,title', 'lowestPrice']
            : ['category:id,title'];

        $products = Product::query()
            ->published()
            ->search($term)
            ->with($relations)
            ->orderByStock('desc')
            ->latest('products.updated_at')
            ->limit(10)
            ->get();

        $results = $products->map(function (Product $product) use ($canViewPrice) {
            $item = [
                'id'       => $product->id,
                'title'    => $product->title,
                'category' => optional($product->category)->title ?: '',
                'image'    => $product->imageUrl('/no-image-product.png'),
                'can_view_price' => $canViewPrice,
                'link'     => $product->link(),
                'in_stock' => (bool) ($product->in_stock ?? $product->getPrices()->exists()),
            ];

            if ($canViewPrice) {
                $item['price'] = $product->getLowestPrice();
            }

            return $item;
        });

        return response()->json($results)
            ->header('Cache-Control', 'private, no-store, max-age=0')
            ->header('Vary', 'Cookie');
    }
}

<?php

namespace Themes\DefaultTheme\src\Controllers;

use App\Models\Cart;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function show()
    {
        $city_id = auth()->check() && auth()->user()->address  ? auth()->user()->address->city_id : null;

        $cart = get_cart();
        $stockReview = $cart ? app(\App\Services\Inventory\CartStockAvailability::class)->forCart($cart) : null;

        return view('front::cart', compact('city_id', 'stockReview'));
    }

    public function availability(Product $product, Request $request)
    {
        $validated = $request->validate([
            'price_id' => ['nullable', 'integer'], 'quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'items' => ['nullable', 'array', 'max:50'], 'items.*.price_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);
        $items = $validated['items'] ?? [['price_id' => $validated['price_id'] ?? 0, 'quantity' => $validated['quantity'] ?? 1]];
        $cart = auth()->user()->cart;
        $existing = $cart ? DB::table('cart_product')->where('cart_id', $cart->id)->where('product_id', $product->id)->get()->groupBy('price_id') : collect();
        $lines = [];
        $service = app(\App\Services\Inventory\CartStockAvailability::class);
        foreach ($items as $item) {
            $price = $product->prices()->find($item['price_id']);
            if (!$price) return response()->json(['status' => 'error', 'errors' => ['مدل انتخاب‌شده اکنون قابل سفارش نیست.']]);
            $price->setRelation('product', $product);
            $lines[] = $service->priceLine($price, (int) $item['quantity'] + (int) $existing->get($price->id, collect())->sum('quantity'));
        }
        $review = $service->check($lines);
        return response()->json(['status' => $review['status'] ? 'success' : 'error', 'errors' => $review['errors'], 'lines' => $review['lines']])->header('Cache-Control', 'no-store, private');
    }

    public function store(Product $product, Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100000',
        ]);

        if ($product->isSinglePrice()) {
            $price = $product->getPrices()->first();
        } else {
            $request->validate([
                'price_id' => ['required', Rule::exists('prices', 'id')->where('product_id', $product->id)->where('deleted_at', null)]
            ]);

            $price = $product->prices()->find($request->price_id);
        }

        if (!$price) return response(['status' => 'error', 'message' => 'مدل انتخاب‌شده اکنون قابل سفارش نیست.'], 422);

        // create or get user cart
        if (auth()->check()) {
            $cart = auth()->user()->getCart();
        } else {
            $cart_id = Cookie::get('cart_id');

            if (!$cart_id || !($cart = Cart::find($cart_id)) || $cart->user_id != null) {
                $cart = Cart::create([
                    'user_id' => null,
                ]);
            }

            Cookie::queue(Cookie::make('cart_id', $cart->id));
        }

        $cart_product = $cart->products()->where('product_id', $product->id)->where('price_id', $price->id)->first();

        // ARIYA_SALES_CONTROL_CART_STORE_V1
        // Sales allocation is product-wide; all variants in this cart count together.
        $currentProductQuantity = (int) DB::table('cart_product')
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->sum('quantity');

        $requestedProductQuantity = $currentProductQuantity + (int) $request->quantity;

        $salesControlStatus = app(\App\Services\Sales\SalesControlService::class)
            ->validateQuantity(
                (int) $product->id,
                $requestedProductQuantity
            );

        if (!$salesControlStatus['status']) {
            return response([
                'status' => 'error',
                'message' => $salesControlStatus['message'],
            ], 422);
        }

        if (!$cart_product) {

            $stock_status = app(\App\Services\Inventory\CartStockAvailability::class)->forPrice($price, (int) $request->quantity);

            if (!$stock_status['status']) {
                return response(['status' => 'error', 'message' => $stock_status['message']]);
            }

            $cart->products()->attach([
                $product->id => [
                    'product_id' => $product->id,
                    'quantity'   => $request->quantity,
                    'price_id'   => $price->id
                ],
            ]);
        } else {

            if ($product->isDownload()) {
                return response([
                    'status' => 'error',
                    'message' => trans('front::messages.controller.this-file-is-in-cart') 
                ]);
            }

            $stock_status = app(\App\Services\Inventory\CartStockAvailability::class)->forPrice($price, (int) $request->quantity + (int) $cart_product->pivot->quantity);

            if (!$stock_status['status']) {
                return response(['status' => 'error', 'message' => $stock_status['message']]);
            }

            DB::table('cart_product')->where('cart_id', $cart->id)->where('id', $cart_product->pivot->id)->update([
                'quantity'   => $cart_product->pivot->quantity + $request->quantity,
                'updated_at' => now(),
            ]);
        }

        return response(['status' => 'success', 'cart' => view('front::partials.cart')->with('render_cart', $cart)->render()]);
    }

    public function storeVariants(Product $product, Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.price_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        if (!$product->isPhysical() || !$product->usesVariantInventoryCodes()) {
            return response()->json([
                'status' => 'error',
                'message' => 'این محصول امکان سفارش چندمدلی ندارد.',
            ], 422);
        }

        $cart = auth()->user()->getCart();
        $prepared = [];

        foreach ($validated['items'] as $item) {
            $price = $product->prices()
                ->whereKey((int) $item['price_id'])
                ->first();

            if (!$price) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'یکی از مدل‌های انتخاب‌شده معتبر نیست.',
                ], 422);
            }

            $existing = DB::table('cart_product')
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('price_id', $price->id)
                ->first();

            $currentQuantity = $existing ? (int) $existing->quantity : 0;
            $finalQuantity = $currentQuantity + (int) $item['quantity'];

            $stockStatus = app(\App\Services\Inventory\CartStockAvailability::class)->forPrice($price, $finalQuantity);

            if (!$stockStatus['status']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $stockStatus['message'],
                ], 422);
            }

            $prepared[] = [
                'price_id' => (int) $price->id,
                'existing' => (bool) $existing,
                'quantity' => $finalQuantity,
            ];
        }

        // ARIYA_SALES_CONTROL_CART_STORE_VARIANTS_V1
        $existingProductQuantity = (int) DB::table('cart_product')
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->sum('quantity');

        $requestedQuantity = array_sum(
            array_map(
                static fn (array $item): int =>
                    (int) $item['quantity'],
                $validated['items']
            )
        );

        $salesControlStatus = app(
            \App\Services\Sales\SalesControlService::class
        )->validateQuantity(
            (int) $product->id,
            $existingProductQuantity + $requestedQuantity
        );

        if (!$salesControlStatus['status']) {
            return response()->json([
                'status' => 'error',
                'message' => $salesControlStatus['message'],
            ], 422);
        }
        DB::transaction(function () use ($prepared, $cart, $product) {
            foreach ($prepared as $item) {
                if ($item['existing']) {
                    DB::table('cart_product')
                        ->where('cart_id', $cart->id)
                        ->where('product_id', $product->id)
                        ->where('price_id', $item['price_id'])
                        ->update([
                            'quantity' => $item['quantity'],
                            'updated_at' => now(),
                        ]);

                    continue;
                }

                DB::table('cart_product')->insert([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'price_id' => $item['price_id'],
                    'quantity' => $item['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $cart->unsetRelation('products');
        $cart->load('products');

        return response()->json([
            'status' => 'success',
            'cart' => view('front::partials.cart')
                ->with('render_cart', $cart)
                ->render(),
        ]);
    }
    public function update(Request $request)
    {
        $cart = get_cart();

        if (!$cart) return response(['errors' => ['سبد خرید یافت نشد.']], 422);
        $rules = [];
        foreach ($cart->products as $product) $rules['product-'.$product->pivot->id] = 'required|integer|min:0|max:100000';
        $request->validate($rules);
        $quantities = [];
        foreach ($cart->products as $product) $quantities[$product->pivot->id] = (int) $request->input('product-'.$product->pivot->id);
        $review = app(\App\Services\Inventory\CartStockAvailability::class)->forCart($cart, $quantities);
        $errors = $review['errors'];
        $salesControlQuantities = [];

        foreach ($cart->products as $product) {

            $price     = $product->prices()->find($product->pivot->price_id);
            $quantity  = $request->input('product-' . $product->pivot->id);
            // The live Inventory preflight above validates all submitted quantities together.

            // ARIYA_SALES_CONTROL_CART_UPDATE_V1
            $finalQuantity = max((int) $quantity, 0);

            if (!isset($salesControlQuantities[$product->id])) {
                $salesControlQuantities[$product->id] = 0;
            }

            $salesControlQuantities[$product->id] += $finalQuantity;
        }

        foreach ($salesControlQuantities as $productId => $quantity) {
            if ($quantity === 0) continue;
            $salesControlStatus = app(\App\Services\Sales\SalesControlService::class)
                ->validateQuantity(
                    (int) $productId,
                    (int) $quantity
                );

            if (!$salesControlStatus['status']) {
                $errors[] = $salesControlStatus['message'];
            }
        }

        if (!empty($errors)) {
            return response(['errors' => $errors], 406);
        }

        foreach ($cart->products as $product) {
            $quantity = $request->input('product-' . $product->pivot->id);

            if ($quantity === '0') {
                DB::table('cart_product')->where('cart_id', $cart->id)->where('id', $product->pivot->id)->delete();
            } else if (intval($quantity) && $product->isPhysical()) {
                DB::table('cart_product')->where('cart_id', $cart->id)->where('id', $product->pivot->id)->update([
                    'quantity' => $quantity,
                    'updated_at' => now(),
                ]);
            }
        }

        return response('success');
    }

    public function destroy($id, Request $request)
    {
        $cart = get_cart();

        if ($cart) {
            DB::table('cart_product')->where('cart_id', $cart->id)->where('id', $id)->delete();
            $cart->unsetRelation('products');
            $cart->load('products');
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'cart' => view('front::partials.cart')
                    ->with('render_cart', $cart)
                    ->render(),
            ]);
        }

        return redirect()->route('front.cart');
    }
}

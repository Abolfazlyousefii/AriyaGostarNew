<?php

namespace Themes\DefaultTheme\src\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Services\Catalog\ProductCatalogService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('name')->latest()->get();

        return view('front::brands.index', compact('brands'));
    }

    public function show(Brand $brand, Request $request)
    {
        $query = Product::detectLang()
            ->published()
            ->where('products.brand_id', $brand->id);

        $data = app(ProductCatalogService::class)->build($query, $request);

        return view('front::brands.show', array_merge($data, [
            'brand' => $brand,
            'catalogTitle' => $brand->name,
            'filterAction' => route('front.brands.show', ['brand' => $brand]),
            'filterResetUrl' => route('front.brands.show', ['brand' => $brand]),
            'showBrandFilter' => false,
        ]));
    }
}
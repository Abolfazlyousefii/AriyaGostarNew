<?php

namespace App\Http\Resources\Datatable\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;

class Product extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'slug'             => $this->slug,
            'image'            => $this->image ? asset($this->image) : asset('/empty.jpg'),
            'title'            => $this->title,
            'product_code'     => $this->product_code,
            'price'            => $this->getLowestPrice(true),
            'category'         => $this->category?->title,
            'categories'       => $this->categories->pluck('title')
                ->prepend($this->category?->title)->filter()->unique()->values()->all(),
            'related_count'    => $this->category ? $this->relatedProducts()->count() : 0,
            'created_at'       => jdate($this->created_at)->format('%d %B %Y'),
            'admin_updated_at' => jdate($this->admin_updated_at)->format('%d %B %Y'),
            'addableToCart'    => $this->addableToCart(),
            'published'        => $this->isPublished(),
            'stock_count'      => $this->prices()->sum('stock'),

            'links' => [
                // Product route binding uses the slug, not the numeric ID.
                'edit'    => route('admin.products.edit', ['product' => $this->slug]),
                'destroy' => route('admin.products.destroy', ['product' => $this->slug]),
                'copy'    => route('admin.products.create', ['product' => $this->slug]),
                'front'   => Route::has('front.products.show')
                    ? route('front.products.show', ['product' => $this->slug])
                    : '#',
            ],
        ];
    }
}

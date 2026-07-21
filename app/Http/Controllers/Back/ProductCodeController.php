<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductCodeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $products = Product::query()
            ->with([
                'prices' => function ($query) {
                    $query->with('get_attributes.group')->orderBy('ordering')->orderBy('id');
                },
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhereHas('prices', function ($query) use ($search) {
                            $query->where('stock_code', 'like', "%{$search}%")
                                ->orWhereHas('get_attributes', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->appends($request->query());

        return view('back.products.codes', compact('products', 'search'));
    }

    public function update(Request $request, Product $product)
    {
        $baseValidator = Validator::make($request->all(), [
            'product_code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('products', 'product_code')->ignore($product->id),
            ],
            'prices' => ['nullable', 'array'],
            'prices.*.id' => ['required', 'integer'],
            'prices.*.stock_code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9._-]+$/',
                'distinct',
            ],
            'prices.*.stock_sync_enabled' => ['nullable', 'boolean'],
        ], [
            'product_code.regex' => 'کد محصول فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره، نقطه و زیرخط باشد.',
            'prices.*.stock_code.regex' => 'کد موجودی فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره، نقطه و زیرخط باشد.',
            'prices.*.stock_code.distinct' => 'کد موجودی تنوع‌ها نباید تکراری باشد.',
        ]);

        $baseValidator->after(function ($validator) use ($request, $product) {
            foreach ((array) $request->input('prices', []) as $index => $item) {
                $price = $product->prices()->withTrashed()->find($item['id'] ?? null);

                if (!$price) {
                    $validator->errors()->add("prices.{$index}.id", 'تنوع انتخاب‌شده متعلق به این محصول نیست.');
                    continue;
                }

                $stockCode = strtoupper(trim((string) ($item['stock_code'] ?? '')));

                $exists = DB::table('prices')
                    ->where('stock_code', $stockCode)
                    ->where('id', '!=', $price->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add("prices.{$index}.stock_code", "کد {$stockCode} قبلاً برای تنوع دیگری ثبت شده است.");
                }
            }
        });

        $validated = $baseValidator->validate();

        DB::transaction(function () use ($product, $validated) {
            $product->forceFill([
                'product_code' => strtoupper(trim($validated['product_code'])),
            ])->save();

            foreach ($validated['prices'] ?? [] as $item) {
                $price = $product->prices()->withTrashed()->findOrFail($item['id']);

                $price->forceFill([
                    'stock_code' => strtoupper(trim($item['stock_code'])),
                    'stock_sync_enabled' => (bool) ($item['stock_sync_enabled'] ?? false),
                ])->save();
            }
        });

        return back()->with('success', 'کدهای محصول و تنوع‌ها با موفقیت ذخیره شدند.');
    }

    public function export(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('search'));

        $products = Product::query()
            ->with(['prices.get_attributes.group'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhereHas('prices', function ($query) use ($search) {
                            $query->where('stock_code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('id')
            ->get();

        $filename = 'warehouse-product-codes-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");

            fputcsv($stream, [
                'شناسه محصول',
                'عنوان محصول',
                'کد مادر محصول',
                'شناسه تنوع',
                'عنوان تنوع',
                'کد موجودی',
                'موجودی فعلی',
                'همگام‌سازی فعال',
            ]);

            foreach ($products as $product) {
                foreach ($product->prices as $price) {
                    fputcsv($stream, [
                        $product->id,
                        $product->title,
                        $product->product_code,
                        $price->id,
                        trim($price->getAttributesValue()) ?: 'قیمت اصلی',
                        $price->stock_code,
                        $price->stock,
                        $price->stock_sync_enabled ? 'بله' : 'خیر',
                    ]);
                }
            }

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

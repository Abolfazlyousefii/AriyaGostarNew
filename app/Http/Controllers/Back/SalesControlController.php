<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SalesControl;
use App\Services\Sales\SalesControlService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalesControlController extends Controller
{
    public function index(Request $request, SalesControlService $service)
    {
        $this->authorize('products');

        $controls = SalesControl::query()
            ->with(['product.prices'])
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $controls->getCollection()->transform(function (SalesControl $control) use ($service) {
            if ($control->product) {
                $control->setAttribute(
                    'central_stock',
                    $service->centralStock($control->product)
                );

                $control->setAttribute(
                    'site_stock',
                    (int) $control->product->prices->sum('stock')
                );
            }

            $control->setAttribute(
                'committed_quantity',
                $service->committedQuantity($control)
            );

            $control->setAttribute(
                'remaining_quantity',
                $service->remaining($control)
            );

            $control->setAttribute(
                'sales_control_status',
                $service->status($control)
            );

            return $control;
        });

        $selectedProduct = null;
        $selectedControl = null;
        $selectedCentralStock = null;
        $selectedSiteStock = null;
        $selectedCommitted = 0;
        $selectedRemaining = null;

        if ($request->filled('product_id')) {
            $selectedProduct = Product::query()
                ->with('prices')
                ->findOrFail((int) $request->product_id);

            $selectedControl = SalesControl::query()
                ->where('product_id', $selectedProduct->id)
                ->first();

            $selectedCentralStock = $service->centralStock($selectedProduct);
            $selectedSiteStock = (int) $selectedProduct->prices->sum('stock');

            if ($selectedControl) {
                $selectedCommitted = $service->committedQuantity($selectedControl);
                $selectedRemaining = $service->remaining($selectedControl);
            }
        }

        $search = trim((string) $request->input('q', ''));

        $searchProducts = collect();

        if ($search !== '') {
            $searchProducts = Product::query()
                ->select([
                    'id',
                    'title',
                    'product_code',
                    'published',
                    'price_type',
                    'type',
                ])
                ->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('product_code', 'like', '%' . $search . '%');

                    if (ctype_digit($search)) {
                        $query->orWhere('id', (int) $search);
                    }
                })
                ->latest('id')
                ->limit(25)
                ->get();
        }

        return view('back.sales-control.index', compact(
            'controls',
            'search',
            'searchProducts',
            'selectedProduct',
            'selectedControl',
            'selectedCentralStock',
            'selectedSiteStock',
            'selectedCommitted',
            'selectedRemaining'
        ));
    }

    public function save(Request $request)
    {
        $this->authorize('products');

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],

            'sale_limit' => ['nullable', 'integer', 'min:0', 'max:100000000'],

            'discount_type' => [
                'nullable',
                Rule::in(['percent', 'amount']),
            ],
            'discount_value' => ['nullable', 'integer', 'min:1', 'max:1000000000000'],
            'discount_starts_at' => ['nullable', 'date'],
            'discount_ends_at' => ['nullable', 'date'],

            'discount_box_limit' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'discount_box_order' => ['nullable', 'integer', 'min:0', 'max:100000'],

            'new_arrivals_limit' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'new_arrivals_order' => ['nullable', 'integer', 'min:0', 'max:100000'],

            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $discountEnabled = $request->boolean('discount_enabled');

        if ($discountEnabled) {
            if (empty($data['discount_type']) || empty($data['discount_value'])) {
                throw ValidationException::withMessages([
                    'discount_value' => 'برای تخفیف فعال، نوع و مقدار تخفیف الزامی است.',
                ]);
            }

            if (
                $data['discount_type'] === 'percent'
                && (int) $data['discount_value'] > 100
            ) {
                throw ValidationException::withMessages([
                    'discount_value' => 'درصد تخفیف نمی‌تواند بیشتر از ۱۰۰ باشد.',
                ]);
            }

            if (empty($data['discount_ends_at'])) {
                throw ValidationException::withMessages([
                    'discount_ends_at' => 'زمان پایان تخفیف الزامی است.',
                ]);
            }

            $start = !empty($data['discount_starts_at'])
                ? Carbon::parse($data['discount_starts_at'])
                : now();

            $end = Carbon::parse($data['discount_ends_at']);

            if ($end->lte($start)) {
                throw ValidationException::withMessages([
                    'discount_ends_at' => 'زمان پایان باید بعد از زمان شروع باشد.',
                ]);
            }

            $data['discount_starts_at'] = $start;
            $data['discount_ends_at'] = $end;
        } else {
            $data['discount_type'] = null;
            $data['discount_value'] = null;
            $data['discount_starts_at'] = null;
            $data['discount_ends_at'] = null;
        }

        $control = SalesControl::query()
            ->where('product_id', (int) $data['product_id'])
            ->first();

        $isNew = !$control;

        if (!$control) {
            $control = new SalesControl([
                'product_id' => (int) $data['product_id'],
                'created_by' => auth()->id(),
                'counter_started_at' => now(),
            ]);
        }

        $control->fill([
            'enabled' => $request->boolean('enabled'),

            'sale_limit' => $data['sale_limit'] ?? null,

            'discount_enabled' => $discountEnabled,
            'discount_type' => $data['discount_type'] ?? null,
            'discount_value' => $data['discount_value'] ?? null,
            'discount_starts_at' => $data['discount_starts_at'] ?? null,
            'discount_ends_at' => $data['discount_ends_at'] ?? null,

            'show_in_discount_box' => $request->boolean('show_in_discount_box'),
            'discount_box_limit' => $data['discount_box_limit'] ?? null,
            'discount_box_order' => $data['discount_box_order'] ?? 100,

            'show_in_new_arrivals' => $request->boolean('show_in_new_arrivals'),
            'new_arrivals_limit' => $data['new_arrivals_limit'] ?? null,
            'new_arrivals_order' => $data['new_arrivals_order'] ?? 100,

            'notes' => $data['notes'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        if ($isNew || $request->boolean('reset_counter')) {
            $control->counter_started_at = now();
        }

        $control->save();

        toastr()->success('تنظیمات کنترل فروش و ویترین ذخیره شد.');

        return redirect()->route('admin.sales-control.index', [
            'product_id' => $control->product_id,
        ]);
    }

    public function reset(SalesControl $salesControl)
    {
        $this->authorize('products');

        $salesControl->update([
            'counter_started_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        toastr()->success('شمارنده سهم فروش از همین لحظه ریست شد.');

        return back();
    }

    public function destroy(SalesControl $salesControl)
    {
        $this->authorize('products');

        $salesControl->delete();

        toastr()->success('کنترل فروش محصول حذف شد.');

        return redirect()->route('admin.sales-control.index');
    }
}
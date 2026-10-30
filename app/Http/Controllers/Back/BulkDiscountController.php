<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\BulkDiscountCampaign;
use App\Models\BulkDiscountItem;
use App\Models\Category;
use App\Models\Price;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\Jalalian;
use App\Services\Sales\BulkDiscountService;

class BulkDiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:products.update');
    }

    private function indexView(array $form = [], ?array $preview = null)
    {
        $rawCategories = Category::query()
            ->where('type', 'productcat')->orderBy('ordering')
            ->get(['id', 'category_id', 'title']);
        $categoriesById = $rawCategories->keyBy('id');
        $categories = $rawCategories->map(function ($category) use ($categoriesById) {
            $depth = 0;
            $parent = $category->category_id;
            $seen = [(int) $category->id => true];
            while ($parent && isset($categoriesById[$parent]) && $depth < 12 && !isset($seen[$parent])) {
                $depth++;
                $seen[$parent] = true;
                $parent = $categoriesById[$parent]->category_id;
            }
            $category->tree_label = str_repeat('— ', $depth) . $category->title;
            return $category;
        });

        $allProducts = Product::query()->orderBy('title')->get(['id', 'title', 'category_id']);
        $productsById = $allProducts->keyBy('id');
        $productsByCategory = $allProducts->groupBy('category_id');
        // Respect both primary category and secondary category_product assignments.
        foreach (DB::table('category_product')->get(['category_id', 'product_id']) as $pivot) {
            $product = $productsById->get($pivot->product_id);
            if (!$product) { continue; }
            $bucket = $productsByCategory->get($pivot->category_id, collect());
            if (!$bucket->contains('id', $product->id)) {
                $bucket->push($product);
                $productsByCategory->put($pivot->category_id, $bucket);
            }
        }
        // Count unique products across every descendant, including pivot categories.
        $children = $rawCategories->groupBy('category_id');
        $productIdsForCategory = [];
        foreach ($productsByCategory as $categoryId => $bucket) {
            $productIdsForCategory[(int) $categoryId] = $bucket->pluck('id')->map(fn ($id) => (int) $id)->all();
        }
        $categoryCounts = [];
        $walk = function ($id, $path = []) use (&$walk, &$categoryCounts, $children, $productIdsForCategory) {
            if (in_array($id, $path, true)) { return []; }
            $ids = $productIdsForCategory[$id] ?? [];
            foreach ($children->get($id, collect()) as $child) {
                $ids = array_merge($ids, $walk((int) $child->id, array_merge($path, [$id])));
            }
            $ids = array_values(array_unique($ids));
            $categoryCounts[$id] = count($ids);
            return $ids;
        };
        foreach ($rawCategories as $category) { $walk((int) $category->id); }

        return view('back.bulk-discounts.index', [
            'campaigns' => BulkDiscountCampaign::query()->withCount('items')->latest('id')->paginate(12),
            'categories' => $categories,
            'categoryCounts' => $categoryCounts,
            'rootCategories' => $categories->filter(fn ($cat) => !$cat->category_id || !$categoriesById->has($cat->category_id)),
            'products' => $allProducts,
            'productsByCategory' => $productsByCategory,
            'form' => $form,
            'preview' => $preview,
        ]);
    }

    public function index()
    {
        return $this->indexView();
    }

    private function validateCampaign(Request $request): array
    {
        $data = Validator::make($request->all(), [
            'title' => 'required|string|max:150',
            'discount_type' => 'required|in:percent,fixed_price',
            'discount_value' => 'required|integer|min:1|max:999999999',
            'round_step' => 'required|integer|in:0,100,500,1000,5000,10000',
            'round_mode' => 'required|in:nearest,up,down',
            'starts_at' => 'nullable|string|max:24',
            'ends_at' => 'nullable|string|max:24',
            'scope' => 'required|in:all,categories,products',
            'category_ids' => 'nullable|array|max:100',
            'category_ids.*' => 'integer|exists:categories,id',
            'product_ids' => 'nullable|array|max:2000',
            'product_ids.*' => 'integer|exists:products,id',
            'exclude_product_ids' => 'nullable|array|max:2000',
            'exclude_product_ids.*' => 'integer|exists:products,id',
            'replace_existing' => 'nullable|boolean',
            'step' => 'required|in:preview,apply',
        ])->validate();

        if ($data['discount_type'] === 'percent' && (int) $data['discount_value'] > 99) {
            throw ValidationException::withMessages(['discount_value' => 'درصد تخفیف باید بین ۱ تا ۹۹ باشد.']);
        }
        if (($data['scope'] === 'categories' && empty($data['category_ids']))
            || ($data['scope'] === 'products' && empty($data['product_ids']))) {
            throw ValidationException::withMessages(['scope' => 'حداقل یک دسته یا محصول انتخاب کنید.']);
        }
        $data['starts_at'] = $this->parseJalaliDate($data['starts_at'] ?? null, 'starts_at');
        $data['ends_at'] = $this->parseJalaliDate($data['ends_at'] ?? null, 'ends_at');
        $this->assertDates($data['starts_at'], $data['ends_at']);
        return $data;
    }

    /** Validate Jalali YYYY/MM/DD HH:mm and persist Gregorian SQL timestamp. */
    private function parseJalaliDate(?string $input, string $field): ?string
    {
        $input = trim((string) $input);
        if ($input === '') { return null; }
        $input = strtr($input, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);
        if (!preg_match('/^[0-9]{4}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}$/D', $input)) {
            throw ValidationException::withMessages([$field => 'تاریخ شمسی باید به صورت ۱۴۰۵/۰۷/۲۸ ۱۲:۳۰ باشد.']);
        }
        try {
            $carbon = Jalalian::fromFormat('Y/m/d H:i', $input)->toCarbon();
            $reformatted = strtr(Jalalian::fromCarbon($carbon)->format('Y/m/d H:i'), [
                '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
                '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            ]);
            if ($reformatted !== $input) {
                throw new \InvalidArgumentException('Invalid Jalali calendar date');
            }
            return $carbon->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([$field => 'تاریخ شمسی یا ساعت واردشده معتبر نیست.']);
        }
    }

    private function assertDates(?string $starts, ?string $ends): void
    {
        if ($ends && Carbon::parse($ends)->lessThanOrEqualTo($starts ? Carbon::parse($starts) : now())) {
            throw ValidationException::withMessages(['ends_at' => 'زمان پایان باید بعد از شروع و در آینده باشد.']);
        }
    }

    /** Snapshot selection. Later-added products are not silently discounted. */
    private function selectedProductIds(array $data): array
    {
        $query = Product::query()->select('products.id');
        if ($data['scope'] === 'categories') {
            $all = Category::query()->where('type', 'productcat')->get(['id', 'category_id']);
            $selected = array_map('intval', $data['category_ids'] ?? []);
            $queue = $selected;
            $seen = [];
            while ($queue) {
                $id = array_shift($queue);
                if (isset($seen[$id])) { continue; }
                $seen[$id] = true;
                foreach ($all as $node) {
                    if ((int) $node->category_id === $id) { $queue[] = (int) $node->id; }
                }
            }
            $ids = array_keys($seen);
            $query->where(function ($q) use ($ids) {
                $q->whereIn('products.category_id', $ids)
                    ->orWhereHas('categories', fn ($r) => $r->whereIn('categories.id', $ids));
            });
        } elseif ($data['scope'] === 'products') {
            $query->whereIn('products.id', $data['product_ids'] ?? []);
        }

        $exclude = array_map('intval', $data['exclude_product_ids'] ?? []);
        if ($exclude) { $query->whereNotIn('products.id', $exclude); }
        $ids = $query->distinct()->pluck('products.id')->map(fn ($id) => (int) $id)->all();
        sort($ids, SORT_NUMERIC);
        if (!$ids) {
            throw ValidationException::withMessages(['scope' => 'هیچ محصولی در محدوده انتخاب‌شده وجود ندارد.']);
        }
        if (count($ids) > 10000) {
            throw ValidationException::withMessages(['scope' => 'تعداد محصولات از حد مجاز ۱۰٬۰۰۰ عبور کرده است.']);
        }
        return $ids;
    }

    private function previewHash(array $data, array $ids): string
    {
        return hash('sha256', json_encode([
            $data['title'], $data['discount_type'], (int) $data['discount_value'],
            $data['starts_at'] ?? null, $data['ends_at'] ?? null,
            (int) $data['round_step'], $data['round_mode'],
            $data['scope'],
            array_values($data['category_ids'] ?? []),
            array_values($data['product_ids'] ?? []),
            array_values($data['exclude_product_ids'] ?? []),
            (bool) ($data['replace_existing'] ?? false),
            $ids,
        ], JSON_UNESCAPED_UNICODE));
    }

    public function submit(Request $request)
    {
        $data = $this->validateCampaign($request);
        $ids = $this->selectedProductIds($data);
        $hash = $this->previewHash($data, $ids);

        if ($data['step'] === 'preview') {
            $examples = Price::query()->with('product:id,title')
                ->whereIn('product_id', $ids)->where('stock', '>', 0)
                ->orderBy('product_id')->limit(12)->get()
                ->map(function ($price) use ($data) {
                    $regular = (float) $price->regularPrice();
                    $after = BulkDiscountService::calculatePrice(
                        $price, $data['discount_type'], (int) $data['discount_value'],
                        (int) $data['round_step'], $data['round_mode']
                    ) ?? $regular;
                    return [
                        'product' => $price->product->title ?? ('#' . $price->product_id),
                        'variant_id' => $price->id,
                        'before' => $regular,
                        'after' => max(0, min($regular, $after)),
                    ];
                });
            session([
                'ariya_bulk_preview_hash' => $hash,
                'ariya_bulk_preview_time' => time(),
            ]);
            return $this->indexView($request->all(), [
                'products' => count($ids),
                'prices' => Price::whereIn('product_id', $ids)->count(),
                'conflicts' => DB::table('bulk_discount_items')->whereIn('product_id', $ids)->count(),
                'samples' => $examples,
            ]);
        }

        if (!hash_equals((string) session('ariya_bulk_preview_hash', ''), $hash)
            || time() - (int) session('ariya_bulk_preview_time', 0) > 900) {
            throw ValidationException::withMessages(['scope' => 'پیش‌نمایش معتبر نیست یا اطلاعات تغییر کرده است؛ مجدداً پیش‌نمایش بگیرید.']);
        }

        try {
        DB::transaction(function () use ($data, $ids) {
            $existing = DB::table('bulk_discount_items')
                ->whereIn('product_id', $ids)->lockForUpdate()->pluck('product_id')->count();
            if ($existing && empty($data['replace_existing'])) {
                throw ValidationException::withMessages([
                    'scope' => "{$existing} محصول قبلاً تخفیف گروهی دارند. ابتدا حذف کنید یا گزینه جایگزینی را فعال کنید.",
                ]);
            }
            if ($existing) {
                DB::table('bulk_discount_items')->whereIn('product_id', $ids)->delete();
            }
            $campaign = BulkDiscountCampaign::create([
                'title' => $data['title'],
                'discount_type' => $data['discount_type'],
                'discount_value' => $data['discount_value'],
                'round_step' => $data['round_step'],
                'round_mode' => $data['round_mode'],
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'enabled' => true,
                'created_by' => auth()->id(),
            ]);
            $now = now();
            foreach (array_chunk($ids, 200) as $chunk) {
                $rows = [];
                foreach ($chunk as $id) {
                    $rows[] = [
                        'campaign_id' => $campaign->id, 'product_id' => $id,
                        'timer_override' => false, 'created_at' => $now, 'updated_at' => $now,
                    ];
                }
                DB::table('bulk_discount_items')->insert($rows);
            }
        }, 3);
        } catch (\Illuminate\Database\QueryException $e) {
            throw ValidationException::withMessages(['scope' => 'در زمان ثبت، اطلاعات کمپین تغییر کرده یا یک محصول هم‌زمان انتخاب شده است. دوباره پیش‌نمایش بگیرید.']);
        }
        session()->forget(['ariya_bulk_preview_hash', 'ariya_bulk_preview_time']);
        return redirect()->route('admin.bulk-discounts.index')->with('success', 'تخفیف گروهی با موفقیت فعال شد.');
    }

    public function edit(BulkDiscountCampaign $campaign)
    {
        return view('back.bulk-discounts.edit', [
            'campaign' => $campaign,
            'items' => $campaign->items()->with('product:id,title')->orderBy('product_id')->paginate(30),
            'products' => Product::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, BulkDiscountCampaign $campaign)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'discount_type' => 'required|in:percent,fixed_price',
            'discount_value' => 'required|integer|min:1|max:999999999',
            'round_step' => 'required|integer|in:0,100,500,1000,5000,10000',
            'round_mode' => 'required|in:nearest,up,down',
            'starts_at' => 'nullable|string|max:24',
            'ends_at' => 'nullable|string|max:24',
        ]);
        if ($data['discount_type'] === 'percent' && (int) $data['discount_value'] > 99) {
            throw ValidationException::withMessages(['discount_value' => 'درصد باید بین ۱ تا ۹۹ باشد.']);
        }
        $data['starts_at'] = $this->parseJalaliDate($data['starts_at'] ?? null, 'starts_at');
        $data['ends_at'] = $this->parseJalaliDate($data['ends_at'] ?? null, 'ends_at');
        $this->assertDates($data['starts_at'], $data['ends_at']);
        $campaign->update([
            'title' => $data['title'], 'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'round_step' => $data['round_step'],
            'round_mode' => $data['round_mode'],
            'starts_at' => $data['starts_at'] ?? null, 'ends_at' => $data['ends_at'] ?? null,
        ]);
        return back()->with('success', 'تنظیمات کمپین ذخیره شد.');
    }

    public function toggle(BulkDiscountCampaign $campaign)
    {
        $campaign->update(['enabled' => !$campaign->enabled]);
        return back()->with('success', 'وضعیت کمپین تغییر کرد.');
    }

    public function removeProduct(Request $request, BulkDiscountCampaign $campaign)
    {
        $data = $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $campaign->items()->where('product_id', (int) $data['product_id'])->delete();
        return back()->with('success', 'محصول از تخفیف گروهی خارج شد؛ تخفیف دستی آن تغییر نکرد.');
    }

    public function schedule(Request $request, BulkDiscountCampaign $campaign, BulkDiscountItem $item)
    {
        abort_unless((int) $item->campaign_id === (int) $campaign->id, 404);
        $data = $request->validate([
            'timer_override' => 'required|boolean',
            'starts_at' => 'nullable|string|max:24',
            'ends_at' => 'nullable|string|max:24',
        ]);
        if ((bool) $data['timer_override']) {
            $data['starts_at'] = $this->parseJalaliDate($data['starts_at'] ?? null, 'starts_at');
            $data['ends_at'] = $this->parseJalaliDate($data['ends_at'] ?? null, 'ends_at');
            $this->assertDates($data['starts_at'], $data['ends_at']);
        }
        $item->update([
            'timer_override' => (bool) $data['timer_override'],
            'starts_at' => $data['timer_override'] ? ($data['starts_at'] ?? null) : null,
            'ends_at' => $data['timer_override'] ? ($data['ends_at'] ?? null) : null,
        ]);
        return back()->with('success', 'زمان‌بندی محصول ذخیره شد.');
    }

    public function addProducts(Request $request, BulkDiscountCampaign $campaign)
    {
        $data = $request->validate([
            'product_ids' => 'required|array|min:1|max:2000',
            'product_ids.*' => 'required|integer|exists:products,id',
        ]);
        $ids = array_values(array_unique(array_map('intval', $data['product_ids'])));
        DB::transaction(function () use ($campaign, $ids) {
            if (DB::table('bulk_discount_items')->whereIn('product_id', $ids)->lockForUpdate()->pluck('product_id')->isNotEmpty()) {
                throw ValidationException::withMessages(['product_ids' => 'یک یا چند محصول در کمپین دیگری هستند؛ ابتدا آن‌ها را از کمپین قبلی حذف کنید.']);
            }
            $now = now();
            DB::table('bulk_discount_items')->insert(array_map(fn ($id) => [
                'campaign_id' => $campaign->id, 'product_id' => $id,
                'timer_override' => false, 'created_at' => $now, 'updated_at' => $now,
            ], $ids));
        });
        return back()->with('success', 'محصولات به کمپین افزوده شدند.');
    }

    public function destroy(BulkDiscountCampaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('admin.bulk-discounts.index')->with('success', 'کمپین حذف شد؛ قیمت‌های اصلی دست‌نخورده‌اند.');
    }
}

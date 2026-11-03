<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Public model choices are derived exclusively from mapped, sellable guard variants. */
final class QuickWholesaleModelDirectory
{
    /** @return array<string,string> e.g. ['model:22' => 'Apple (iPhone) - iPhone 13'] */
    public function options(): array
    {
        $sitePrices = DB::connection()->table('prices as pr')
            ->join('products as p', 'p.id', '=', 'pr.product_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->whereNull('pr.deleted_at')
            ->where('pr.stock', '>', 0)
            ->where('p.published', 1)
            ->where('p.type', 'physical')
            ->where('c.type', 'productcat')
            ->where(function ($query) {
                $query->where('p.title', 'like', '%گارد%')
                    ->orWhere('p.title', 'like', '%قاب%');
            })
            ->get([
                'pr.external_variant_id', 'pr.product_id', 'pr.variant_code',
                'pr.stock', 'pr.cart_min', 'pr.cart_max',
            ]);

        $mapped = [];
        foreach ($sitePrices as $price) {
            $id = filter_var((string) $price->external_variant_id, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            if ($id === false) {
                continue;
            }
            $mapped[(int) $id][] = $price;
        }
        if ($mapped === []) {
            return [];
        }

        $models = [];
        $inventory = DB::connection('inv');
        foreach (array_chunk(array_keys($mapped), 350) as $ids) {
            $variants = $inventory->table('product_variants as v')
                ->join('model_lists as m', 'm.id', '=', 'v.model_list_id')
                ->whereIn('v.id', $ids)
                ->where('v.is_active', 1)
                ->where('v.sales_enabled', 1)
                ->where('v.stock', '>', 0)
                ->get([
                    'v.id', 'v.product_id', 'v.variant_code', 'v.stock',
                    'm.id as model_id', 'm.brand', 'm.model_name',
                ]);

            foreach ($variants as $variant) {
                foreach ($mapped[(int) $variant->id] ?? [] as $price) {
                    if ((int) $variant->product_id !== (int) $price->product_id) {
                        continue;
                    }
                    $siteCode = trim((string) ($price->variant_code ?? ''));
                    $invCode = trim((string) ($variant->variant_code ?? ''));
                    if ($siteCode !== '' && $invCode !== '' && $siteCode !== $invCode) {
                        continue;
                    }
                    $minimum = max(1, (int) ($price->cart_min ?? 1));
                    $maximum = min((int) $price->stock, (int) $variant->stock);
                    if ($price->cart_max !== null) {
                        $maximum = min($maximum, (int) $price->cart_max);
                    }
                    if ($maximum < $minimum) {
                        continue;
                    }
                    $brand = trim((string) $variant->brand);
                    $model = trim((string) $variant->model_name);
                    if ($brand === '' || $model === '') {
                        continue;
                    }
                    $models[self::key((int) $variant->model_id)] = $brand.' - '.$model;
                    break;
                }
            }
        }
        natcasesort($models);
        return $models;
    }

    /** Compare a concrete phone model with exact or slash-combined Inventory entries. */
    public static function isCompatible(string $brand, string $selectedName, string $candidateName): bool
    {
        $selected = self::normalizeModel($brand, $selectedName);
        $candidate = self::normalizeModel($brand, $candidateName);
        if ($selected === $candidate) {
            return true;
        }
        if (!str_contains($candidate, '/')) {
            return false;
        }

        $parts = explode('/', $candidate);
        $first = trim(array_shift($parts));
        if ($first === '') {
            return false;
        }
        if ($selected === $first) {
            return true;
        }
        // Carry over the common prefix (iPhone, Redmi Note, Galaxy...) only
        // for short trailing fragments; never use fuzzy prefix matching.
        $prefix = preg_replace('/(?:[a-z]*[0-9]+[a-z]*(?:\s+(?:pro|max|plus|4g|5g))?)$/', '', $first);
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            if ($selected === $part || $selected === trim((string) $prefix.$part)) {
                return true;
            }
        }
        return false;
    }

    private static function normalizeModel(string $brand, string $name): string
    {
        $name = QuickWholesalePhoneModelMatcher::normalize($name);
        if (strcasecmp($brand, 'Samsung') === 0) {
            $name = preg_replace('/^galaxy\s+/u', '', $name);
        }
        return trim($name);
    }

    public static function key(int $modelId): string
    {
        return 'model:'.$modelId;
    }

    public static function idFromKey(string $key): ?int
    {
        if (!preg_match('/^model:([1-9][0-9]{0,9})$/D', $key, $match)) {
            return null;
        }
        $id = filter_var($match[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : (int) $id;
    }

    /** Keep previously shared links such as ?model=iPhone%2013 working, without ambiguous brands. */
    public static function resolve(string $input, array $options): ?string
    {
        if (isset($options[$input])) {
            return $input;
        }
        $normalized = QuickWholesalePhoneModelMatcher::normalize($input);
        $matches = [];
        foreach ($options as $key => $label) {
            $parts = explode(' - ', $label, 2);
            $model = $parts[1] ?? $label;
            if (QuickWholesalePhoneModelMatcher::normalize($model) === $normalized
                || QuickWholesalePhoneModelMatcher::normalize($label) === $normalized) {
                $matches[] = $key;
            }
        }
        return count($matches) === 1 ? $matches[0] : null;
    }
}

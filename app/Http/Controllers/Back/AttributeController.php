<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttributeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Attribute::class, 'attribute');
    }

    public function create()
    {
        $attributeGroups = AttributeGroup::all();

        return view('back.attributes.create', compact('attributeGroups'));
    }

    public function store(Request $request)
    {
        $mode = $request->input('mode', 'single');
        $request->merge(['mode' => $mode]);

        $this->validate($request, [
            'mode'               => 'required|in:single,bulk',
            'name'               => 'nullable|required_if:mode,single|string|max:255',
            'names'              => 'nullable|required_if:mode,bulk|string',
            'attribute_group_id' => 'required|exists:attribute_groups,id',
            'value'              => 'nullable|string|max:255',
            'ordering'           => 'nullable|integer|min:0',
        ]);

        $attributeGroup = AttributeGroup::findOrFail($request->attribute_group_id);

        if ($mode === 'bulk' && $attributeGroup->type === 'color') {
            throw ValidationException::withMessages([
                'mode' => 'ثبت گروهی برای گروه‌های رنگی امکان‌پذیر نیست؛ چون هر رنگ باید مقدار رنگ جداگانه داشته باشد.',
            ]);
        }

        if ($mode === 'single') {
            $name = trim((string) $request->name);

            if ($this->attributeExists($attributeGroup->id, $name)) {
                throw ValidationException::withMessages([
                    'name' => 'این ویژگی قبلاً در گروه انتخاب‌شده ثبت شده است.',
                ]);
            }

            Attribute::create([
                'name'               => $name,
                'value'              => $attributeGroup->type === 'color' ? $request->value : null,
                'attribute_group_id' => $attributeGroup->id,
                'ordering'           => $request->ordering,
            ]);

            toastr()->success('ویژگی با موفقیت ایجاد شد.');

            return response('success');
        }

        $names = $this->parseBulkNames((string) $request->names);

        if (empty($names)) {
            throw ValidationException::withMessages([
                'names' => 'حداقل یک عنوان ویژگی وارد کنید.',
            ]);
        }

        $existingNames = Attribute::query()
            ->where('attribute_group_id', $attributeGroup->id)
            ->pluck('name')
            ->mapWithKeys(function ($name) {
                return [$this->normalizeName($name) => true];
            })
            ->all();

        $startOrdering = $request->filled('ordering')
            ? (int) $request->ordering
            : ((int) Attribute::where('attribute_group_id', $attributeGroup->id)->max('ordering') + 1);

        $createdCount = 0;
        $duplicateCount = 0;

        DB::transaction(function () use (
            $names,
            $existingNames,
            $attributeGroup,
            $startOrdering,
            &$createdCount,
            &$duplicateCount
        ) {
            $ordering = $startOrdering;

            foreach ($names as $name) {
                $normalizedName = $this->normalizeName($name);

                if (isset($existingNames[$normalizedName])) {
                    $duplicateCount++;
                    continue;
                }

                Attribute::create([
                    'name'               => $name,
                    'value'              => null,
                    'attribute_group_id' => $attributeGroup->id,
                    'ordering'           => $ordering++,
                ]);

                $existingNames[$normalizedName] = true;
                $createdCount++;
            }
        });

        if ($createdCount === 0) {
            throw ValidationException::withMessages([
                'names' => 'تمام موارد واردشده قبلاً در این گروه ثبت شده‌اند.',
            ]);
        }

        $message = "{$createdCount} ویژگی با موفقیت ایجاد شد.";

        if ($duplicateCount > 0) {
            $message .= " {$duplicateCount} مورد تکراری ثبت نشد.";
        }

        toastr()->success($message);

        return response('success');
    }

    public function edit(Attribute $attribute)
    {
        $attributeGroups = AttributeGroup::all();

        return view('back.attributes.edit', compact('attribute', 'attributeGroups'));
    }

    public function update(Attribute $attribute, Request $request)
    {
        $this->validate($request, [
            'name'               => 'required',
            'attribute_group_id' => 'required|exists:attribute_groups,id'
        ]);

        $attribute->update([
            'name'               => $request->name,
            'value'              => $request->value,
            'attribute_group_id' => $request->attribute_group_id,
            'ordering'           => $request->ordering,
        ]);

        toastr()->success('ویژگی با موفقیت ویرایش شد.');

        return response('success');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return response('success');
    }

    public function sort(Request $request)
    {
        $this->authorize('attributes.update');

        $this->validate($request, [
            'attributes' => 'required|array'
        ]);

        $i = null;

        foreach ($request->input('attributes') as $attribute) {
            $dbAttr = Attribute::findOrFail($attribute);

            if ($i === null) {
                $i = Attribute::whereIn('id', $request->input('attributes'))->min('ordering');
            }

            $dbAttr->update([
                'ordering' => $i++,
            ]);
        };

        return response('success');
    }

    private function parseBulkNames(string $input): array
    {
        $items = preg_split('/[\r\n,،;؛]+/u', $input) ?: [];
        $uniqueItems = [];

        foreach ($items as $item) {
            $name = trim(preg_replace('/^[\s\-–—•*]+/u', '', $item));

            if ($name === '') {
                continue;
            }

            $normalizedName = $this->normalizeName($name);

            if (!isset($uniqueItems[$normalizedName])) {
                $uniqueItems[$normalizedName] = $name;
            }
        }

        return array_values($uniqueItems);
    }

    private function attributeExists(int $attributeGroupId, string $name): bool
    {
        return Attribute::query()
            ->where('attribute_group_id', $attributeGroupId)
            ->get(['name'])
            ->contains(function (Attribute $attribute) use ($name) {
                return $this->normalizeName($attribute->name) === $this->normalizeName($name);
            });
    }

    private function normalizeName(?string $name): string
    {
        $name = trim((string) $name);
        $name = preg_replace('/\s+/u', ' ', $name);

        return mb_strtolower($name, 'UTF-8');
    }
}

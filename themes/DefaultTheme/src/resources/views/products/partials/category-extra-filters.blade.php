@php
    $products_id = $categoryContext
        ->allPublishedProducts()
        ->pluck('id');
@endphp

@foreach(
    $categoryContext
        ->getFilter()
        ->related()
        ->orderBy('ordering')
        ->get() as $filter
)

    @switch($filter->filterable_type)

        @case('App\Models\Specification')
            @php
                $spec_values = \Illuminate\Support\Facades\DB::table(
                    'product_specification'
                )
                    ->whereIn('product_id', $products_id)
                    ->where(
                        'specification_id',
                        $filter->filterable_id
                    )
                    ->get()
                    ->unique('value')
                    ->pluck('value')
                    ->toArray();

                $spec_values = get_separated_values(
                    $spec_values,
                    $filter->separator
                );
            @endphp

            @if(count($spec_values))
                <div class="ariya-catalog-filter__section">
                    <button
                        class="ariya-catalog-filter__section-title collapsed"
                        type="button"
                        data-toggle="collapse"
                        data-target="#catalog-extra-{{ $filter->id }}"
                        aria-expanded="{{ request('filters.' . $filter->id) ? 'true' : 'false' }}"
                    >
                        <span>{{ $filter->filterable->name }}</span>
                        <i class="mdi mdi-chevron-down"></i>
                    </button>

                    <div
                        id="catalog-extra-{{ $filter->id }}"
                        class="collapse {{ request('filters.' . $filter->id) ? 'show' : '' }}"
                    >
                        <div class="ariya-catalog-check-list ariya-catalog-check-list--compact">

                            @foreach($spec_values as $spec_val)
                                @php
                                    $checkId =
                                        'catalog-spec-'
                                        . $filter->id
                                        . '-'
                                        . $loop->index;
                                @endphp

                                <label
                                    class="ariya-catalog-check"
                                    for="{{ $checkId }}"
                                >
                                    <input
                                        id="{{ $checkId }}"
                                        type="checkbox"
                                        name="filters[{{ $filter->id }}][{{ $spec_val }}]"
                                        {{ request('filters.' . $filter->id . '.' . $spec_val) ? 'checked' : '' }}
                                    >

                                    <span class="ariya-catalog-check__box">
                                        <i class="mdi mdi-check"></i>
                                    </span>

                                    <span class="ariya-catalog-check__label">
                                        {{ $spec_val }}
                                    </span>
                                </label>
                            @endforeach

                        </div>
                    </div>
                </div>
            @endif

            @break

        @case('App\Models\AttributeGroup')
            @php
                $price_ids = \Illuminate\Support\Facades\DB::table(
                    'prices'
                )
                    ->whereIn('product_id', $products_id)
                    ->pluck('id');

                $attribute_ids = \Illuminate\Support\Facades\DB::table(
                    'attribute_price'
                )
                    ->whereIn('price_id', $price_ids)
                    ->pluck('attribute_id');

                $group_values = \App\Models\Attribute::where(
                    'attribute_group_id',
                    $filter->filterable_id
                )
                    ->whereIn('id', $attribute_ids)
                    ->orderBy('ordering')
                    ->orderBy('name')
                    ->get();
            @endphp

            @if($group_values->count())
                <div class="ariya-catalog-filter__section">
                    <button
                        class="ariya-catalog-filter__section-title collapsed"
                        type="button"
                        data-toggle="collapse"
                        data-target="#catalog-extra-{{ $filter->id }}"
                        aria-expanded="{{ request('filters.' . $filter->id) ? 'true' : 'false' }}"
                    >
                        <span>{{ $filter->filterable->name }}</span>
                        <i class="mdi mdi-chevron-down"></i>
                    </button>

                    <div
                        id="catalog-extra-{{ $filter->id }}"
                        class="collapse {{ request('filters.' . $filter->id) ? 'show' : '' }}"
                    >
                        <div class="ariya-catalog-check-list ariya-catalog-check-list--compact">

                            @foreach($group_values as $group_val)
                                @php
                                    $checkId =
                                        'catalog-attribute-'
                                        . $filter->id
                                        . '-'
                                        . $group_val->id;
                                @endphp

                                <label
                                    class="ariya-catalog-check"
                                    for="{{ $checkId }}"
                                >
                                    <input
                                        id="{{ $checkId }}"
                                        type="checkbox"
                                        class="attribute_group_input"
                                        name="filters[{{ $filter->id }}][{{ $group_val->id }}]"
                                        {{ request('filters.' . $filter->id . '.' . $group_val->id) ? 'checked' : '' }}
                                    >

                                    <span class="ariya-catalog-check__box">
                                        <i class="mdi mdi-check"></i>
                                    </span>

                                    <span class="ariya-catalog-check__label">
                                        {{ $group_val->name }}
                                    </span>

                                    @if($group_val->group->type === 'color')
                                        <span
                                            class="filter-color"
                                            style="background-color: {{ $group_val->value }}"
                                        ></span>
                                    @endif
                                </label>
                            @endforeach

                        </div>
                    </div>
                </div>
            @endif

            @break

        @case('App\Models\StaticFilter')
            @if(
                $filter->filterable &&
                $filter->filterable->type === 'child_category' &&
                $categoryContext->childrenCategories->count()
            )
                <div class="ariya-catalog-filter__section">
                    <button
                        class="ariya-catalog-filter__section-title collapsed"
                        type="button"
                        data-toggle="collapse"
                        data-target="#catalog-extra-{{ $filter->id }}"
                        aria-expanded="{{ request('filters.' . $filter->id) ? 'true' : 'false' }}"
                    >
                        <span>دسته‌بندی زیرمجموعه</span>
                        <i class="mdi mdi-chevron-down"></i>
                    </button>

                    <div
                        id="catalog-extra-{{ $filter->id }}"
                        class="collapse {{ request('filters.' . $filter->id) ? 'show' : '' }}"
                    >
                        <div class="ariya-catalog-check-list ariya-catalog-check-list--compact">

                            @foreach(
                                $categoryContext->childrenCategories
                                as $childCategory
                            )
                                @php
                                    $checkId =
                                        'catalog-child-'
                                        . $filter->id
                                        . '-'
                                        . $childCategory->id;
                                @endphp

                                <label
                                    class="ariya-catalog-check"
                                    for="{{ $checkId }}"
                                >
                                    <input
                                        id="{{ $checkId }}"
                                        type="checkbox"
                                        name="filters[{{ $filter->id }}][{{ $childCategory->id }}]"
                                        {{ request('filters.' . $filter->id . '.' . $childCategory->id) ? 'checked' : '' }}
                                    >

                                    <span class="ariya-catalog-check__box">
                                        <i class="mdi mdi-check"></i>
                                    </span>

                                    <span class="ariya-catalog-check__label">
                                        {{ $childCategory->title }}
                                    </span>
                                </label>
                            @endforeach

                        </div>
                    </div>
                </div>
            @endif

            @break

    @endswitch

@endforeach
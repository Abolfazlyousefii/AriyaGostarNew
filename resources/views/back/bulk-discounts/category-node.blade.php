<details class="bd4-node" data-bd4-category data-category-name="{{ $categoryNode->title }}" {{ $depth === 0 ? 'open' : '' }}>
    <summary class="bd4-node-head">
        <span class="bd4-node-toggle" aria-hidden="true">▸</span>
        @if($productsMode)
            <span class="bd4-node-title">{{ $categoryNode->title }}</span>
            <button type="button" class="bd4-choose-branch" data-bd4-select-branch title="انتخاب تمام محصولات این شاخه">انتخاب شاخه</button>
        @else
            <label class="bd4-node-label" onclick="event.stopPropagation()">
                <input type="checkbox" data-bd4-category-choice name="category_ids[]" value="{{ $categoryNode->id }}" {{ in_array($categoryNode->id, (array)($form['category_ids'] ?? [])) ? 'checked' : '' }}>
                <span>{{ $categoryNode->title }}</span>
            </label>
        @endif
        <span class="bd4-count">{{ number_format($categoryCounts[(int)$categoryNode->id] ?? 0) }}</span>
    </summary>
    <div class="bd4-node-children">
        @if($productsMode)
            @foreach($productsByCategory->get($categoryNode->id, collect()) as $nestedProduct)
                <label class="bd4-product-row" data-bd4-product-row data-product-name="{{ mb_strtolower($nestedProduct->title) }}">
                    <input type="checkbox" data-bd4-product-choice name="product_ids[]" value="{{ $nestedProduct->id }}" {{ in_array($nestedProduct->id, (array)($form['product_ids'] ?? [])) ? 'checked' : '' }}>
                    <span>{{ $nestedProduct->title }}</span>
                    <small>#{{ $nestedProduct->id }}</small>
                </label>
            @endforeach
        @endif
        @foreach($categories->where('category_id', $categoryNode->id) as $childNode)
            @include('back.bulk-discounts.category-node', ['categoryNode' => $childNode, 'depth' => $depth + 1, 'productsMode' => $productsMode])
        @endforeach
    </div>
</details>

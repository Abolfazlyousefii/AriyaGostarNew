<li class="dd-item" data-id="{{ $child_category->id }}">
    <div class="dd-handle">
        <div class="category-list-main">
            <span class="category-list-icon" data-category-icon="{{ $child_category->slug }}" aria-hidden="true">
                @if($child_category->menu_icon)
                    <img src="{{ asset($child_category->menu_icon) }}" alt="">
                @else
                    <i class="feather icon-grid"></i>
                @endif
            </span>

            <span class="category-title">{{ $child_category->title }}</span>

            @if(!$child_category->published)
                <span class="category-status-badge">منتشر نشده</span>
            @endif
        </div>

        <div class="category-actions dd-nodrag">
            <a data-category="{{ $child_category->slug }}"
               class="edit-category dd-nodrag"
               href="javascript:void(0)">
                <i class="fa fa-pencil text-info"></i>
                ویرایش
            </a>

            <a data-category="{{ $child_category->slug }}"
               class="delete-category dd-nodrag"
               href="javascript:void(0)"
               data-toggle="modal"
               data-target="#modal-delete">
                <i class="fa fa-trash text-danger"></i>
                حذف
            </a>
        </div>
    </div>

    @if ($child_category->childrenCategories->isNotEmpty())
        <ol class="dd-list">
            @foreach ($child_category->childrenCategories as $child)
                @include('back.partials.child_category', ['child_category' => $child])
            @endforeach
        </ol>
    @endif
</li>

@switch($menu->static_type)
    @case('products')
        @if($productcats->count())
            @once
                <link rel="stylesheet" href="{{ theme_asset('css/ariya-exact-megamenu.css') }}?v=7">

<style>
/* ARIYA_TASK02_3A04_INLINE_V1 */

/* Desktop trigger */
header.ariya-site-header .axm-root > .axm-trigger {
    color: #334155 !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    text-decoration: none !important;
}

header.ariya-site-header .axm-root:hover > .axm-trigger,
header.ariya-site-header .axm-root:focus-within > .axm-trigger,
header.ariya-site-header .axm-root.is-open > .axm-trigger {
    color: #176abd !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    text-decoration: none !important;
}

header.ariya-site-header .axm-trigger-grid {
    display: none !important;
}

header.ariya-site-header .axm-trigger-chevron {
    color: #176abd !important;
}

/* Dropdown */
header.ariya-site-header .axm-panel {
    border-color: #e1eaf5 !important;
    background: #fff !important;
}

header.ariya-site-header .axm-sidebar {
    background: #f9fbff !important;
    border-right-color: #e1eaf5 !important;
}

/* Main categories */
header.ariya-site-header .axm-category.is-active,
header.ariya-site-header .axm-category:hover,
header.ariya-site-header .axm-category:focus {
    color: #16569e !important;
    background: #edf5ff !important;
    text-decoration: none !important;
}

header.ariya-site-header .axm-category.is-active {
    box-shadow: inset -3px 0 0 #1686d9 !important;
}

header.ariya-site-header .axm-category.is-active .axm-category-chevron,
header.ariya-site-header .axm-category:hover .axm-category-chevron {
    color: #176abd !important;
}

/* Category icon container */
header.ariya-site-header .axm-category.is-active .axm-category-icon,
header.ariya-site-header .axm-category:hover .axm-category-icon,
header.ariya-site-header .axm-category:focus .axm-category-icon {
    background-color: #e1efff !important;
    border-color: #d1e4f9 !important;
    color: #176abd !important;
}

/* Category title and view-all */
header.ariya-site-header .axm-current-category,
header.ariya-site-header .axm-current-category:hover {
    color: #176abd !important;
    text-decoration: none !important;
}

header.ariya-site-header .axm-view-all {
    color: #176abd !important;
    background: #f1f7ff !important;
    border-color: #cde2f8 !important;
    text-decoration: none !important;
}

header.ariya-site-header .axm-view-all:hover,
header.ariya-site-header .axm-view-all:focus {
    color: #104c91 !important;
    background: #dfedff !important;
    border-color: #accff4 !important;
    text-decoration: none !important;
}

/* Subcategories */
header.ariya-site-header .axm-group-title::before {
    background: #1686d9 !important;
}

header.ariya-site-header .axm-group-title:hover,
header.ariya-site-header .axm-group-title:focus,
header.ariya-site-header .axm-child-link:hover,
header.ariya-site-header .axm-child-link:focus {
    color: #176abd !important;
    text-decoration: none !important;
}

/* ARIYA_TASK02_3A05_STABLE_V1 */

/* Remove the empty pointer gap between trigger and dropdown. */
header.ariya-site-header .axm-root > .axm-panel {
    top: 100% !important;
    transform: none !important;
    transition:
        opacity .18s ease,
        visibility .18s ease !important;
}

/* Keep dropdown stable while hovered or keyboard-focused. */
header.ariya-site-header .axm-root:hover > .axm-panel,
header.ariya-site-header .axm-root:focus-within > .axm-panel,
header.ariya-site-header .axm-root.is-open > .axm-panel {
    transform: none !important;
}

/* Visible keyboard focus without shifting the layout. */
header.ariya-site-header .axm-trigger:focus-visible,
header.ariya-site-header .axm-category:focus-visible,
header.ariya-site-header .axm-current-category:focus-visible,
header.ariya-site-header .axm-view-all:focus-visible,
header.ariya-site-header .axm-group-title:focus-visible,
header.ariya-site-header .axm-child-link:focus-visible {
    outline: 2px solid #176abd !important;
    outline-offset: -2px !important;
    text-decoration: none !important;
}

/* Respect reduced-motion accessibility preferences. */
@media (prefers-reduced-motion: reduce) {
    header.ariya-site-header .axm-root > .axm-panel {
        transition: none !important;
    }
}
</style>

            @endonce

            <li class="list-item axm-root" data-axm-root>
                <a class="nav-link axm-trigger"
                   href="{{ route('front.products.index') }}"
                   aria-haspopup="true"
                   aria-expanded="false">
                    <span>{{ $menu->title ?: 'دسته‌بندی کالاها' }}</span>
                    <i class="mdi mdi-chevron-up axm-trigger-chevron" aria-hidden="true"></i>
                </a>

                <div class="axm-panel" data-axm-panel hidden>
                    <div class="axm-main">
                        @foreach($productcats as $category)
                            @php($children = $category->categories)
                            <section class="axm-pane {{ $loop->first ? 'is-active' : '' }}"
                                     data-axm-pane="{{ $category->id }}"
                                     aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                                <div class="axm-pane-head">
                                    <a href="{{ $category->link }}" class="axm-current-category">
                                        <span>{{ $category->title }}</span>
                                        <i class="mdi mdi-chevron-left" aria-hidden="true"></i>
                                    </a>

                                    <a href="{{ $category->link }}" class="axm-view-all">
                                        <i class="mdi mdi-arrow-right" aria-hidden="true"></i>
                                        <span>مشاهده همه محصولات</span>
                                    </a>
                                </div>

                                <div class="axm-columns">
                                    @forelse($children as $childCategory)
                                        <div class="axm-group">
                                            <a class="axm-group-title" href="{{ $childCategory->link }}">
                                                {{ $childCategory->title }}
                                            </a>

                                            @foreach($childCategory->categories as $child2)
                                                <a class="axm-child-link" href="{{ $child2->link }}">
                                                    {{ $child2->title }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @empty
                                        <div class="axm-empty">هنوز زیر‌دسته‌ای برای این بخش ثبت نشده است.</div>
                                    @endforelse
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <aside class="axm-sidebar" aria-label="دسته‌های اصلی محصولات">
                        @foreach($productcats as $category)
                            <a href="{{ $category->link }}"
                               class="axm-category {{ $loop->first ? 'is-active' : '' }}"
                               data-axm-category="{{ $category->id }}">
                                <span class="axm-category-icon" aria-hidden="true">
                                    @if(!empty($category->menu_icon))
                                        <img src="{{ asset($category->menu_icon) }}" alt="" width="32" height="32" loading="lazy">
                                    @else
                                        <i class="mdi mdi-view-grid"></i>
                                    @endif
                                </span>
                                <span class="axm-category-title">{{ $category->title }}</span>
                                <i class="mdi mdi-chevron-left axm-category-chevron" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </aside>
                </div>
            </li>

            @once
                <script src="{{ theme_asset('js/ariya-exact-megamenu.js') }}?v=6" defer></script>
            @endonce
        @endif
        @break

    @case('posts')
        @if($postcats->count())
            <li class="list-item list-item-has-children menu-col-1">
                <a class="nav-link" href="{{ route('front.posts.index') }}">{{ $menu->title }}</a>
                <ul class="sub-menu nav">
                    @foreach($postcats as $category)
                        @include('front::partials.menu.child-category', ['category' => $category])
                    @endforeach
                </ul>
            </li>
        @endif
        @break
@endswitch

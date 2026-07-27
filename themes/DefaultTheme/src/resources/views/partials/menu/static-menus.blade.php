@switch($menu->static_type)
    @case('products')
        @if($productcats->count())
            @once
                <link rel="stylesheet" href="{{ theme_asset('css/ariya-exact-megamenu.css') }}?v=6">
            @endonce

            <li class="list-item axm-root" data-axm-root>
                <a class="nav-link axm-trigger"
                   href="{{ route('front.products.index') }}"
                   aria-haspopup="true"
                   aria-expanded="false">
                    <span class="axm-trigger-grid" aria-hidden="true">
                        <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                    </span>
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
                                        <i class="mdi mdi-view-grid-outline"></i>
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

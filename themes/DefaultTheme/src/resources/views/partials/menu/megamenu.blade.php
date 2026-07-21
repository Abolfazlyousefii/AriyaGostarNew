@php
    $megaChildren = $menu->childrenmenus;

    $megaLink = function ($item) {
        if ($item->type === 'category' && $item->category) {
            return $item->category->link;
        }

        return $item->link ?: '#';
    };

    $megaTitle = function ($item) {
        if ($item->type === 'category' && $item->category) {
            return $item->title ?: $item->category->title;
        }

        return $item->title;
    };
@endphp

<li class="list-item list-item-has-children position-static ariya-mega-root">
    <a class="nav-link ariya-mega-trigger" href="{{ $menu->link ?: '#' }}" aria-haspopup="true" aria-expanded="false">
        <span class="ariya-mega-trigger-icon" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
        <span class="ariya-mega-trigger-title">{{ $menu->title }}</span>
        <span class="ariya-mega-trigger-arrow" aria-hidden="true"></span>
    </a>

    <div class="ariya-mega-shell" role="menu" aria-hidden="true">
        <aside class="ariya-mega-sidebar" aria-label="دسته‌بندی‌های اصلی">
            @foreach($megaChildren as $childMenu)
                @php
                    $panelId = 'ariya-mega-panel-' . $menu->id . '-' . $childMenu->id;
                    $childTitle = $megaTitle($childMenu);
                    $childLink = $megaLink($childMenu);
                @endphp

                <a
                    href="{{ $childLink }}"
                    class="ariya-mega-sidebar-item {{ $loop->first ? 'is-active' : '' }}"
                    data-mega-target="{{ $panelId }}"
                    aria-controls="{{ $panelId }}"
                    aria-current="{{ $loop->first ? 'true' : 'false' }}"
                >
                    <span class="ariya-mega-sidebar-icon" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                    <span class="ariya-mega-sidebar-title">{{ $childTitle }}</span>
                    <span class="ariya-mega-sidebar-arrow" aria-hidden="true"></span>
                </a>
            @endforeach
        </aside>

        <section class="ariya-mega-content" aria-live="polite">
            @forelse($megaChildren as $childMenu)
                @php
                    $panelId = 'ariya-mega-panel-' . $menu->id . '-' . $childMenu->id;
                    $childTitle = $megaTitle($childMenu);
                    $childLink = $megaLink($childMenu);

                    $categoryChildren = collect();
                    if ($childMenu->type === 'category' && $childMenu->category) {
                        $categoryChildren = $childMenu->category->getCategories();
                    }
                @endphp

                <div
                    id="{{ $panelId }}"
                    class="ariya-mega-panel {{ $loop->first ? 'is-active' : '' }}"
                    data-mega-panel="{{ $panelId }}"
                    aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                >
                    <div class="ariya-mega-panel-head">
                        <a class="ariya-mega-current-link" href="{{ $childLink }}">
                            <span>{{ $childTitle }}</span>
                            <i aria-hidden="true"></i>
                        </a>

                        <a class="ariya-mega-view-all" href="{{ $childLink }}">
                            <i aria-hidden="true"></i>
                            <span>مشاهده همه محصولات</span>
                        </a>
                    </div>

                    <div class="ariya-mega-groups">
                        @if($childMenu->type === 'category' && $childMenu->category)
                            @foreach($categoryChildren as $childCategory)
                                <div class="ariya-mega-group">
                                    <a class="ariya-mega-group-title" href="{{ $childCategory->link }}">
                                        <span>{{ $childCategory->title }}</span>
                                    </a>

                                    @foreach($childCategory->getCategories() as $childCategory2)
                                        <a class="ariya-mega-group-link" href="{{ $childCategory2->link }}">{{ $childCategory2->title }}</a>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif

                        @foreach($childMenu->childrenmenus as $childMenu2)
                            @php
                                $childMenu2Title = $megaTitle($childMenu2);
                                $childMenu2Link = $megaLink($childMenu2);
                            @endphp

                            <div class="ariya-mega-group">
                                <a class="ariya-mega-group-title" href="{{ $childMenu2Link }}">
                                    <span>{{ $childMenu2Title }}</span>
                                </a>

                                @if($childMenu2->type === 'category' && $childMenu2->category)
                                    @foreach($childMenu2->category->getCategories() as $childCategory2)
                                        <a class="ariya-mega-group-link" href="{{ $childCategory2->link }}">{{ $childCategory2->title }}</a>
                                    @endforeach
                                @endif

                                @foreach($childMenu2->childrenmenus as $childMenu3)
                                    <a class="ariya-mega-group-link" href="{{ $megaLink($childMenu3) }}">{{ $megaTitle($childMenu3) }}</a>
                                @endforeach
                            </div>
                        @endforeach

                        @if(
                            ($childMenu->type !== 'category' || !$childMenu->category || $categoryChildren->isEmpty())
                            && $childMenu->childrenmenus->isEmpty()
                        )
                            <div class="ariya-mega-empty">
                                <span>برای این دسته هنوز زیرمجموعه‌ای ثبت نشده است.</span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="ariya-mega-empty ariya-mega-empty-root">
                    <span>دسته‌بندی‌ای برای نمایش وجود ندارد.</span>
                </div>
            @endforelse
        </section>
    </div>
</li>

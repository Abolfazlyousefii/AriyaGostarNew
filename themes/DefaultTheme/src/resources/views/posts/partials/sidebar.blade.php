
@if($latest_posts->count())
    <div class="{{ !empty($native_sticky) ? 'col-xl-3 col-12' : 'col-lg-3 col-md-4 col-sm-12 col-12' }} mb-3 sidebar {{ !empty($native_sticky) ? 'ariya-article-sidebar' : 'sticky-sidebar' }}">
        <div class="widget-posts dt-sn dt-sl mb-3 {{ !empty($native_sticky) ? 'ariya-article-sidebar__sticky' : '' }}">
            <div class="title-sidebar dt-sl mb-3">
                <h3>{{ trans('front::messages.posts.the-latest-posts') }}</h3>
            </div>
            <div class="content-sidebar dt-sl">
                @foreach($latest_posts as $item)
                    <div class="item">
                        <div class="item-inner">
                            <div class="item-thumb">
                                <a href="{{ route('front.posts.show', ['post' => $item]) }}" class="img-holder" style="background-image: url('{{ $item->image ? asset($item->image) : theme_asset('images/blog-empty-image.jpg') }}')"></a>
                            </div>
                            <p class="title">
                                <a href="{{ route('front.posts.show', ['post' => $item]) }}">{{ $item->title }}</a>
                            </p>
                            <div class="item-meta">
                                <span class="time">{{ jdate($item->created_at)->format('%d %B %Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
@endif

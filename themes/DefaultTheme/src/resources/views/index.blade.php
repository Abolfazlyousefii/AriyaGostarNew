@extends('front::layouts.master')

@push('meta')
@if(option('allow_indexing_main_page') == "off")
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="bingbot" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow"/>
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
@endif
    <meta name="description" content="{{ option('info_short_description') }}">
    <meta name="keywords" content="{{ option('info_tags') }}">
    <link rel="canonical" href="{{ url('/') }}" />
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "url": "{{ route('front.index') }}",
            "name": "{{ option('site_title') }}",
            "logo": "{{ option('info_logo') ? asset(option('info_logo')) : asset(config('front.asset_path') . 'img/logo.png') }}",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "{{ route('front.products.search') }}/?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
    </script>
@endpush

@section('content')
    <main class="main-content dt-sl mb-3 ariya-home-main">
        @foreach ($widgets as $widget)
            @if(in_array($widget->key, ['fullscreen-slider', 'main-slider']))
                <div class="ariya-home-slider-fullbleed">
                    @if($widget->key === 'fullscreen-slider')
                        @include('front::widgets.fullscreen-slider')
                    @else
                        @include('front::widgets.main-slider')
                    @endif
                </div>
            @else
                <div class="container main-container ariya-home-widget-container">
                    @switch($widget->key)
                        @case('products-default-block')
                            @include('front::widgets.products-default-block')
                        @break
                        @case('products-colorful-block')
                            @include('front::widgets.products-colorful-block')
                        @break
                        @case('middle-banners')
                            @include('front::widgets.middle-banners')
                        @break
                        @case('coworker-sliders')
                            @include('front::widgets.coworker-sliders')
                        @break
                        @case('sevices-sliders')
                            @include('front::widgets.sevices-sliders')
                        @break
                        @case('categories')
                            @include('front::widgets.categories')
                        @break
                        @case('posts')
                            @include('front::widgets.posts')
                        @break
                        @case('products-discount-block')
                            @include('front::widgets.discount-products')
                        @break
                        @case('custom-page')
                            @include('front::widgets.custom-page')
                        @break
                    @endswitch
                </div>
            @endif
        @endforeach
    </main>
@endsection

@push('scripts')
    <script>
        function showIndexModal(name) {
            let now = new Date().getTime();
            let prevShowDate = localStorage.getItem(name);
            if (!prevShowDate || (Math.abs(now - prevShowDate) / 3600000) >= 24) {
                setTimeout(() => {
                    $(name).modal('show');
                    localStorage.setItem(name, new Date().getTime());
                }, 5000);
            }
        }
    </script>

    @if (option('dt_index_popup_type') == 'image')
        <div id="image-popup-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                    <div class="modal-body">
                        <a href="{{ option('dt_index_popup_link') }}">
                            <div class="d-none d-md-block"><img src="{{ asset(option('dt_index_popup_image')) }}" class="img-responsive w-100"></div>
                            <div class="d-block d-md-none"><img src="{{ asset(option('dt_index_popup_image_mobile')) }}" class="img-responsive w-100"></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script>showIndexModal('#image-popup-modal');</script>
    @elseif (option('dt_index_popup_type') == 'text')
        <div id="text-popup-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
                    <div class="modal-body pt-0"><p>{!! option('dt_index_popup_text') !!}</p></div>
                </div>
            </div>
        </div>
        <script>showIndexModal('#text-popup-modal');</script>
    @endif
@endpush

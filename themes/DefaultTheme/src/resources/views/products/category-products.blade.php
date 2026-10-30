@extends('front::layouts.master', ['title' => $category->title])

{{-- ARIYA_DESKTOP_SORT_TABS_V1 --}}
@push('styles')
    @include('front::products.partials.ariya-desktop-sort-styles')
@endpush

@push('meta')
    @if(option('allow_indexing_categories') == "off")
        <meta name="robots" content="noindex, nofollow">
        <meta name="googlebot" content="noindex, nofollow">
        <meta name="bingbot" content="noindex, nofollow">
    @else
        <meta name="robots" content="index, follow">
        <meta name="googlebot" content="index, follow">
        <meta name="bingbot" content="index, follow">
    @endif

    <meta
        property="og:title"
        content="{{ $category->meta_title ?: $category->title }}"
    >

    <meta
        property="og:url"
        content="{{ url()->current() }}"
    >

    <meta
        name="description"
        content="{{ $category->meta_description ?: $category->description }}"
    >

    <meta
        name="keywords"
        content="{{ $category->getTags }}"
    >

    <link
        rel="canonical"
        href="{{ url()->current() }}"
    >

@endpush

@section('content')
    <main class="main-content dt-sl mt-4 mb-3 ariya-products-page">
        <div class="container main-container">

            <div class="title-breadcrumb-special dt-sl mb-3">
                <div class="breadcrumb dt-sl">
                    <nav>
                        <a href="/">
                            {{ trans('front::messages.products.home') }}
                        </a>

                        <a href="{{ route('front.products.index') }}">
                            {{ trans('front::messages.products.products') }}
                        </a>

                        @foreach($category->parents() as $parent)
                            <a
                                href="{{ route(
                                    'front.products.category',
                                    ['category' => $parent]
                                ) }}"
                            >
                                {{ $parent->title }}
                            </a>
                        @endforeach

                        <span>{{ $category->title }}</span>
                    </nav>
                </div>
            </div>

            @include('front::products.partials.catalog-panel')

            @if($category->description)

                <div class="row mt-2">
                    <div class="dt-sl dt-sn search-amazing-tab mb-3 mx-3">
                        <div class="row">
                            <div
                                class="col-md-12 p-md-5 category-background"
                                style="background-image: url({{ asset($category->background_image) }});"
                            >
                                {!! $category->description !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </main>
@endsection

{{-- ARIYA_FIVE_CARDS_GRID_V1 --}}
@push('styles')
    @include('front::products.partials.ariya-five-cards-style')
@endpush

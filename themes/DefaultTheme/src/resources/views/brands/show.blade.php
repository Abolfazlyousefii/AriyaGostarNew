@extends('front::layouts.master', ['title' => $brand->name])

@push('meta')
    <meta
        property="og:title"
        content="{{ $brand->name }}"
    >

    <link
        rel="canonical"
        href="{{ route(
            'front.brands.show',
            ['brand' => $brand]
        ) }}"
    >
@endpush

@section('content')
    <main class="main-content dt-sl mt-4 mb-3 ariya-products-page">
        <div class="container main-container">

            <div class="title-breadcrumb-special dt-sl mb-3">
                <div class="breadcrumb dt-sl">
                    <nav>
                        <a href="/">خانه</a>
                        <span>{{ $brand->name }}</span>
                    </nav>
                </div>
            </div>

            @include('front::products.partials.catalog-panel')

            @if($brand->description)
                <div class="row mt-2">
                    <div class="dt-sl dt-sn search-amazing-tab mb-3 mx-3">
                        <div class="row">
                            <div class="col-md-12 p-md-4">
                                {!! $brand->description !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </main>
@endsection
@extends(
    'front::layouts.master',
    ['title' => $catalogTitle]
)

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

                        <span>{{ $catalogTitle }}</span>
                    </nav>
                </div>
            </div>

            @include('front::products.partials.catalog-panel')

        </div>
    </main>
@endsection
@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('back/assets/css/pages/products-modern.css') }}?v=1">
@endpush

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-12 mb-2">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb no-border">
                            <li class="breadcrumb-item">مدیریت</li>
                            <li class="breadcrumb-item">محصولات</li>
                            <li class="breadcrumb-item active">ویرایش محصول</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div id="main-card" class="content-body">
                <form class="form" id="product-edit-form" action="{{ route('admin.products.update', ['product' => $product->id]) }}" data-redirect="{{ route('admin.products.index') }}" method="post">
                    @csrf
                    @method('put')
                    @include('back.products.partials.modern-form', [
                        'formProduct' => $product,
                        'isEdit' => true,
                    ])
                </form>

                <div id="form-progress" class="progress progress-bar-success progress-xl" style="display:none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
                </div>
            </div>
        </div>
    </div>

    @include('back.products.partials.specification-template')
    @include('back.products.partials.prices-template')
    @include('back.products.partials.files-template')
@endsection

@include('back.partials.plugins', ['plugins' => ['ckeditor', 'jquery-tagsinput', 'jquery.validate', 'jquery-ui', 'jquery-ui-sortable', 'dropzone', 'persian-datepicker']])

@php
    $help_videos = [config('general.video-helpes.products-create')];
@endphp

@push('scripts')
    <script>
        var mockImages = [];
        @foreach ($product->gallery()->orderBy('ordering')->get() as $image)
            mockImages.push({
                name: @json($image->image),
                galleryImage: true,
                type: 'image/jpeg',
                status: 'success',
                upload: { filename: @json($image->image) },
                prevFile: true,
                accepted: true,
                image: @json($image->image),
                color_id: @json($image->color_id),
            });
        @endforeach

        var product = {{ $product->id }};
        var groupCount = {{ $product->specificationGroups->unique()->count() }};
        var specificationCount = {{ $product->specifications->unique()->count() }};
        var availableTypes = [
            @foreach ($specTypes as $spec_type)
                @json($spec_type->name),
            @endforeach
        ];
        var specifications_type_first_change = true;
        var priceCount = {{ $product->prices()->count() }};
        var filesCount = {{ $product->files()->count() }};
        var sizesCount = {{ $product->sizes()->count() }};
    </script>
    <script src="{{ asset('back/assets/js/pages/products/all.js') }}?v=14"></script>
    <script src="{{ asset('back/assets/js/pages/products/edit.js') }}?v=9"></script>
    <script src="{{ asset('back/assets/js/pages/products/modern-form.js') }}?v=1"></script>
@endpush

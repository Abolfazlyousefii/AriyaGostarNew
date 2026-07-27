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
                            <li class="breadcrumb-item active">ایجاد محصول</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div id="main-card" class="content-body">
                <form class="form" id="product-create-form" action="{{ route('admin.products.store') }}" method="post">
                    @csrf
                    @include('back.products.partials.modern-form', [
                        'formProduct' => $copy_product,
                        'isEdit' => false,
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
        var groupCount = $('.specification-group').length;
        var specificationCount = $('.single-specificition').length;
        var availableTypes = [
            @foreach ($specTypes as $spec_type)
                @json($spec_type->name),
            @endforeach
        ];
        var specifications_type_first_change = {{ $copy_product ? 'true' : 'false' }};
        var priceCount = {{ $copy_product ? $copy_product->prices->count() : 0 }};
        var filesCount = 0;
        var sizesCount = {{ $copy_product ? $copy_product->sizes()->count() : 0 }};
    </script>
    <script src="{{ asset('back/assets/js/pages/products/all.js') }}?v=14"></script>
    <script src="{{ asset('back/assets/js/pages/products/create.js') }}?v=5"></script>
    <script src="{{ asset('back/assets/js/pages/products/modern-form.js') }}?v=1"></script>
@endpush

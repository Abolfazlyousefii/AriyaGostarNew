@extends('back.layouts.master')

@section('content')

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb no-border">
                                <li class="breadcrumb-item">مدیریت</li>
                                <li class="breadcrumb-item">محصولات</li>
                                <li class="breadcrumb-item active">ایمپورت محصولات از SQL</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">
                <div class="col-md-8 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">ایمپورت محصولات از فایل SQL</h4>
                        </div>

                        <div class="card-content">
                            <div class="card-body">

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="alert alert-warning">
                                    قبل از ایمپورت، حتماً از دیتابیس بکاپ بگیرید. این فایل روی محصولات، قیمت‌ها و دسته‌بندی‌ها اثر می‌گذارد.
                                </div>

                                <form action="{{ route('admin.products.sqlImport.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="form-group">
                                        <label>انتخاب فایل SQL</label>
                                        <input type="file" name="sql_file" class="form-control" accept=".sql,.txt" required>
                                        <small class="text-muted">فقط فایل‌های .sql یا .txt قابل قبول هستند.</small>
                                    </div>

                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary">
                                            ایمپورت فایل SQL
                                        </button>

                                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                            بازگشت به لیست محصولات
                                        </a>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">راهنما</h4>
                        </div>

                        <div class="card-body">
                            <p>
                                فایل SQL باید همان فایل تبدیل‌شده مخصوص قالب آریا باشد.
                            </p>
                            <p class="mb-0">
                                اگر فایل خام سایت قبلی را مستقیم وارد کنید، ممکن است ساختار دیتابیس خراب شود.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
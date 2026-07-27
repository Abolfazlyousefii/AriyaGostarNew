@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" href="{{ asset('back/app-assets/plugins/nestable2/jquery.nestable.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/plugins/jquery-tagsinput/jquery.tagsinput.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/plugins/jquery-ui/jquery-ui.css') }}">

    <style>
        .category-manager-wrap {
            width: 100%;
            max-width: 100%;
            margin: 0;
        }

        .category-create-box {
            padding: 18px;
            background: #fbfcff;
            border: 1px solid #ebeef5;
            border-radius: 10px;
        }

        .category-create-grid {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) minmax(300px, 420px) 180px;
            align-items: end;
            gap: 16px;
        }

        .category-create-field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #4d5260;
        }

        .category-icon-picker {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-icon-preview {
            flex: 0 0 46px;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #9ca3af;
            background: #fff;
            border: 1px solid #e1e5ee;
            border-radius: 9px;
        }

        .category-icon-preview img {
            display: none;
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .category-icon-preview.has-image img {
            display: block;
        }

        .category-icon-preview.has-image i {
            display: none;
        }

        .category-icon-input {
            min-width: 0;
            flex: 1 1 auto;
        }

        .category-create-submit {
            min-height: 38px;
            width: 100%;
            white-space: nowrap;
        }

        .category-create-help {
            display: block;
            margin-top: 6px;
            color: #98a0ad;
            font-size: 12px;
        }

        .category-create-feedback {
            display: none;
            margin-top: 12px;
            padding: 9px 12px;
            border-radius: 7px;
            font-size: 13px;
        }

        .category-create-feedback.is-success {
            display: block;
            color: #1f7a48;
            background: #eaf8f0;
            border: 1px solid #c9ebd7;
        }

        .category-create-feedback.is-error {
            display: block;
            color: #b42318;
            background: #fff1f0;
            border: 1px solid #ffd0cc;
        }

        .category-list-box {
            margin-top: 24px;
        }

        .category-list-box .dd-handle {
            min-height: 48px;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 7px 12px !important;
            background: #f8f9fc;
            border: 1px solid #e3e6ee;
            border-radius: 7px;
        }

        .category-list-main {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-list-icon {
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #9ca3af;
            background: #fff;
            border: 1px solid #e2e6ee;
            border-radius: 7px;
        }

        .category-list-icon img {
            width: 28px;
            height: 28px;
            object-fit: contain;
        }

        .category-title {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 500;
        }

        .category-status-badge {
            padding: 2px 7px;
            color: #9b2c2c;
            background: #fff2f2;
            border-radius: 10px;
            font-size: 10px;
        }

        .category-actions {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .category-actions a {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        @media (max-width: 1199.98px) {
            .category-create-grid {
                grid-template-columns: 1fr 1fr;
            }

            .category-create-action {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 767.98px) {
            .category-create-grid {
                grid-template-columns: 1fr;
            }

            .category-create-action {
                grid-column: auto;
            }

            .category-list-box .dd-handle {
                align-items: flex-start;
                flex-direction: column;
            }

            .category-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
@endpush

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
                                    <li class="breadcrumb-item">مدیریت محصولات</li>
                                    <li class="breadcrumb-item active">دسته‌بندی‌ها</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-md-right col-md-3 col-12 d-md-block d-none">
                    <div class="form-group breadcrum-right">
                        <div id="save-changes" class="spinner-border text-success" role="status" style="display: none">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section id="description" class="card">
                    <div class="card-header">
                        <h4 class="card-title">مدیریت دسته‌بندی‌ها</h4>
                    </div>

                    <div id="main-block" class="card-content">
                        <div class="card-body">
                            <div class="category-manager-wrap">
                                <form id="create-category"
                                      class="category-create-box"
                                      action="{{ route('admin.categories.store') }}"
                                      method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="type" value="productcat">

                                    <div class="category-create-grid">
                                        <div class="category-create-field">
                                            <label for="title">نام دسته‌بندی</label>
                                            <input id="title"
                                                   type="text"
                                                   class="form-control"
                                                   name="title"
                                                   placeholder="نام دسته‌بندی جدید را وارد کنید..."
                                                   required>
                                        </div>

                                        <div class="category-create-field">
                                            <label for="create-menu-icon">آیکن مگامنو</label>
                                            <div class="category-icon-picker">
                                                <div id="create-menu-icon-preview" class="category-icon-preview" aria-hidden="true">
                                                    <i class="feather icon-image"></i>
                                                    <img src="" alt="پیش‌نمایش آیکن">
                                                </div>

                                                <div class="category-icon-input">
                                                    <div class="custom-file">
                                                        <input id="create-menu-icon"
                                                               type="file"
                                                               accept="image/png,image/jpeg,image/webp"
                                                               name="menu_icon"
                                                               class="custom-file-input">
                                                        <label class="custom-file-label" for="create-menu-icon">انتخاب آیکن</label>
                                                    </div>
                                                    <small class="category-create-help">PNG یا WebP شفاف، ترجیحاً 64×64 پیکسل</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="category-create-action">
                                            <button type="submit" class="btn btn-success category-create-submit waves-effect waves-light">
                                                <i class="feather icon-save ml-50"></i>
                                                افزودن و ثبت دسته‌بندی
                                            </button>
                                        </div>
                                    </div>

                                    <div id="create-category-feedback" class="category-create-feedback" role="status"></div>
                                </form>

                                <div class="dd category-list-box">
                                    <ol class="dd-list">
                                        @foreach ($categories as $category)
                                            @include('back.partials.child_category', ['child_category' => $category])
                                        @endforeach
                                    </ol>
                                </div>

                                <p class="card-text mt-3">
                                    <i class="feather icon-info mr-1 align-middle"></i>
                                    <span class="text-info">برای ایجاد زیر‌دسته، آیتم موردنظر را بکشید و کمی به سمت چپ رها کنید. تغییر ترتیب به‌صورت خودکار ذخیره می‌شود.</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel19" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel19">آیا مطمئن هستید؟</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    با حذف این دسته‌بندی تمامی زیر‌دسته‌های آن حذف خواهند شد. آیا مطمئن هستید؟
                </div>
                <form action="" method="POST" id="delete-form">
                    @csrf
                    @method('DELETE')
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success waves-effect waves-light" data-dismiss="modal">خیر</button>
                        <button type="submit" class="btn btn-danger waves-effect waves-light">بله، حذف شود</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 900px; width: 90vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">ویرایش دسته‌بندی</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="edit-form" action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" style="max-height: 72vh; overflow-y: auto;"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger waves-effect waves-light" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">
                            <i class="feather icon-save ml-50"></i>
                            ذخیره تغییرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/nestable2/jquery.nestable.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-tagsinput/jquery.tagsinput.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-ui/jquery-ui.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/ckeditor/ckeditor.js') }}"></script>

    <script>
        var maxDepth = 10;
        var deleteRouteBase = '{{ route("admin.products.categories.destroy", "") }}';
        var BASE_URL = '{{ url('/') }}';
        var adminRoutePrefix = '{{ admin_route_prefix() }}';
    </script>

    <script src="{{ asset('back/assets/js/pages/categories.js') }}?v=2"></script>
@endpush

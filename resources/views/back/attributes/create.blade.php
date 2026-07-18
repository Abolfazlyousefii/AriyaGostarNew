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
                                <li class="breadcrumb-item">مدیریت ویژگی‌ها</li>
                                <li class="breadcrumb-item active">ایجاد ویژگی</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <section class="card">
                <div class="card-header">
                    <h4 class="card-title">ایجاد ویژگی جدید</h4>
                </div>

                <div id="main-card" class="card-content">
                    <div class="card-body">
                        <div class="col-12 col-md-10 offset-md-1">
                            <div class="alert alert-info mb-2" role="alert">
                                برای ساخت مدل‌های گوشی، حالت «ثبت گروهی» را انتخاب کن و هر مدل را در یک خط وارد کن. موارد تکراری به‌صورت خودکار نادیده گرفته می‌شوند.
                            </div>

                            <form class="form" id="attribute-create-form" action="{{ route('admin.attributes.store') }}">
                                @csrf

                                <div class="form-body">
                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label>گروه ویژگی‌ها</label>
                                                <select class="form-control" name="attribute_group_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($attributeGroups as $attributeGroup)
                                                        <option
                                                            data-type="{{ $attributeGroup->type }}"
                                                            value="{{ $attributeGroup->id }}"
                                                        >
                                                            {{ $attributeGroup->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label class="d-block">روش ثبت</label>

                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input
                                                        type="radio"
                                                        id="attribute-mode-bulk"
                                                        name="mode"
                                                        value="bulk"
                                                        class="custom-control-input"
                                                        checked
                                                    >
                                                    <label class="custom-control-label" for="attribute-mode-bulk">ثبت گروهی</label>
                                                </div>

                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input
                                                        type="radio"
                                                        id="attribute-mode-single"
                                                        name="mode"
                                                        value="single"
                                                        class="custom-control-input"
                                                    >
                                                    <label class="custom-control-label" for="attribute-mode-single">ثبت تکی</label>
                                                </div>

                                                <small id="color-mode-hint" class="form-text text-warning" style="display: none;">
                                                    ویژگی‌های رنگی باید به‌صورت تکی ثبت شوند تا برای هر مورد رنگ جدا تعیین شود.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="bulk-fields">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label>نام ویژگی‌ها</label>
                                                    <textarea
                                                        class="form-control"
                                                        name="names"
                                                        rows="12"
                                                        placeholder="iPhone 13&#10;iPhone 14&#10;Samsung A15&#10;Samsung A25&#10;Redmi Note 13"
                                                    ></textarea>
                                                    <small class="form-text text-muted">
                                                        هر مدل را در یک خط وارد کن. جداکردن با ویرگول فارسی یا انگلیسی و «؛» هم پشتیبانی می‌شود.
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="single-fields" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>نام</label>
                                                    <input type="text" class="form-control" name="name">
                                                </div>
                                            </div>

                                            <div id="color-select-div" class="col-md-6 col-12" style="display: none;">
                                                <div class="form-group">
                                                    <label>مقدار رنگ</label>
                                                    <input type="color" class="form-control" name="value" value="#000000">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 col-12">
                                            <div class="form-group">
                                                <label>ترتیب شروع</label>
                                                <input type="number" min="0" class="form-control" name="ordering">
                                                <small class="form-text text-muted">
                                                    اختیاری است. در ثبت گروهی، ترتیب موارد بعدی به‌صورت خودکار ادامه پیدا می‌کند.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 text-right">
                                            <button id="attribute-submit-button" type="submit" class="btn btn-primary mb-1 waves-effect waves-light">
                                                ثبت ویژگی‌ها
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/localization/messages_fa.min.js') }}"></script>

    <script src="{{ asset('back/assets/js/pages/attributes/create.js') }}?v=3"></script>
@endpush

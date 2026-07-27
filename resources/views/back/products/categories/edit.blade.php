<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>نام دسته‌بندی</label>
            <input id="edit-title" type="text" name="title" class="form-control" value="{{ $category->title }}" required>
        </div>
    </div>

    <div class="col-md-6">
        <fieldset class="form-group">
            <label>تصویر دسته‌بندی</label>
            <div class="custom-file">
                <input id="category-image" type="file" accept="image/*" name="image" class="custom-file-input">
                <label class="custom-file-label" for="category-image">{{ $category->image ?: 'انتخاب تصویر' }}</label>
            </div>
            <small>بهترین اندازه <span class="text-danger">{{ config('front.imageSizes.CategoryImage') }}</span> پیکسل است.</small>
        </fieldset>
    </div>

    <div class="col-md-6">
        <fieldset class="form-group">
            <label>آیکن مگامنو</label>
            <div class="d-flex align-items-center">
                <div id="edit-menu-icon-preview" class="category-icon-preview ml-1 {{ $category->menu_icon ? 'has-image' : '' }}">
                    <i class="feather icon-image"></i>
                    <img src="{{ $category->menu_icon ? asset($category->menu_icon) : '' }}" alt="پیش‌نمایش آیکن">
                </div>

                <div class="custom-file flex-grow-1">
                    <input id="menu_icon"
                           type="file"
                           accept="image/png,image/jpeg,image/webp"
                           name="menu_icon"
                           class="custom-file-input">
                    <label class="custom-file-label" for="menu_icon">انتخاب آیکن جدید</label>
                </div>
            </div>
            <small class="text-muted d-block mt-50">پس از انتخاب، دکمه «ذخیره تغییرات» پایین پنجره را بزنید.</small>
        </fieldset>
    </div>

    <div class="col-md-6">
        <div class="row">
            <div class="col-md-6">
                <fieldset class="form-group">
                    <label>نوع فیلتر</label>
                    <select id="filter_type" name="filter_type" class="form-control">
                        <option value="inherit" {{ $category->filter_type == 'inherit' ? 'selected' : '' }}>ارث‌بری از دسته بالاتر</option>
                        <option value="none" {{ $category->filter_type == 'none' ? 'selected' : '' }}>بدون فیلتر</option>
                        <option value="filterId" {{ $category->filter_type == 'filterId' ? 'selected' : '' }}>انتخاب فیلتر</option>
                    </select>
                </fieldset>
            </div>
            <div class="col-md-6">
                <fieldset class="form-group">
                    <label>انتخاب فیلتر</label>
                    <select id="filter_id" name="filter_id" class="form-control">
                        @foreach ($filters as $filter)
                            <option value="{{ $filter->id }}" {{ $category->filter_id == $filter->id ? 'selected' : '' }}>{{ $filter->title }}</option>
                        @endforeach
                    </select>
                </fieldset>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <fieldset class="form-group">
            <label>تصویر پس‌زمینه</label>
            <div class="custom-file">
                <input id="background-image" type="file" accept="image/*" name="background_image" class="custom-file-input">
                <label class="custom-file-label" for="background-image">{{ $category->background_image ?: 'انتخاب تصویر' }}</label>
            </div>
        </fieldset>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>عنوان سئو</label>
            <input type="text" name="meta_title" class="form-control" value="{{ $category->meta_title }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>URL</label>
            <input id="slug" type="text" class="form-control" name="slug" value="{{ $category->slug }}">
            <p class="mb-0 mt-50">
                <small>
                    <a id="generate-category-slug" href="#">ایجاد خودکار</a>
                    <span id="slug-spinner" class="spinner-grow spinner-grow-sm text-success" role="status" style="display: none;">
                        <span class="sr-only">Loading...</span>
                    </span>
                </small>
            </p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>توضیحات سئو</label>
            <textarea class="form-control" name="meta_description" rows="3">{{ $category->meta_description }}</textarea>
        </div>
    </div>

    <div class="col-md-6">
        <fieldset class="form-group">
            <label>کلمات کلیدی</label>
            <input type="text" name="tags" class="form-control tags" value="{{ $category->getTags }}">
        </fieldset>
    </div>

    <div class="col-md-4">
        <fieldset class="checkbox form-group">
            <div class="vs-checkbox-con vs-checkbox-primary">
                <input type="checkbox" name="published" {{ $category->published ? 'checked' : '' }}>
                <span class="vs-checkbox">
                    <span class="vs-checkbox--check">
                        <i class="vs-icon feather icon-check"></i>
                    </span>
                </span>
                <span>نمایش دسته‌بندی در سایت</span>
            </div>
        </fieldset>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>توضیحات</label>
            <textarea id="category-description" class="form-control" name="description" rows="3">{{ $category->description }}</textarea>
        </div>
    </div>
</div>

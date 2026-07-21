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
                                    <li class="breadcrumb-item active">کدهای انبار</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="card">
                    <div class="card-header">
                        <div>
                            <h4 class="card-title">مدیریت کد محصولات و تنوع‌ها</h4>
                            <p class="text-muted mb-0 mt-50">کد موجودی هر تنوع، کلید اتصال آن کالا به نرم‌افزار انبار است.</p>
                        </div>
                        <a href="{{ route('admin.product-codes.export', request()->query()) }}" class="btn btn-outline-success">
                            <i class="feather icon-download"></i> خروجی CSV
                        </a>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.product-codes.index') }}" class="row align-items-end">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label for="product-code-search">جستجو در نام محصول، کد محصول، کد موجودی یا مدل</label>
                                        <input id="product-code-search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="مثلاً ARY-V-0000000123 یا iPhone 15 Pro Max">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group d-flex">
                                        <button class="btn btn-primary flex-grow-1" type="submit">جستجو</button>
                                        @if($search !== '')
                                            <a class="btn btn-outline-secondary mr-50" href="{{ route('admin.product-codes.index') }}">پاک کردن</a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                @forelse($products as $product)
                    <form method="POST" action="{{ route('admin.product-codes.update', $product) }}" class="card mb-2 product-code-card">
                        @csrf
                        @method('PUT')

                        <div class="card-header border-bottom">
                            <div class="d-flex align-items-center">
                                <img src="{{ $product->imageUrl() }}" alt="{{ $product->title }}" style="width:56px;height:56px;object-fit:cover;border-radius:6px" class="ml-1">
                                <div>
                                    <h5 class="mb-25">{{ $product->title }}</h5>
                                    <small class="text-muted">{{ $product->prices->count() }} ردیف قیمت / تنوع</small>
                                </div>
                            </div>
                            <a href="{{ route('admin.products.edit', $product) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="feather icon-edit"></i> ویرایش محصول
                            </a>
                        </div>

                        <div class="card-content">
                            <div class="card-body">
                                <div class="row align-items-end mb-2">
                                    <div class="col-md-7">
                                        <label>کد مادر محصول</label>
                                        <div class="input-group ltr">
                                            <input type="text" name="product_code" value="{{ old('product_code', $product->product_code) }}" class="form-control inventory-code-input" maxlength="64" required>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary copy-inventory-code" type="button" data-copy="{{ $product->product_code }}">کپی</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <small class="text-muted">این کد برای شناسایی محصول مادر است. موجودی با کد تنوع همگام می‌شود.</small>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>مدل / تنوع</th>
                                                <th style="min-width:240px">کد موجودی در انبار</th>
                                                <th>موجودی فعلی</th>
                                                <th>همگام‌سازی</th>
                                                <th>آخرین وضعیت</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($product->prices as $price)
                                                <tr>
                                                    <td>
                                                        <strong>{{ trim($price->getAttributesValue()) ?: 'قیمت اصلی محصول' }}</strong>
                                                        <input type="hidden" name="prices[{{ $loop->index }}][id]" value="{{ $price->id }}">
                                                    </td>
                                                    <td>
                                                        <div class="input-group ltr">
                                                            <input type="text" name="prices[{{ $loop->index }}][stock_code]" value="{{ $price->stock_code }}" class="form-control inventory-code-input" maxlength="64" required>
                                                            <div class="input-group-append">
                                                                <button class="btn btn-outline-secondary copy-inventory-code" type="button" data-copy="{{ $price->stock_code }}">کپی</button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-light-primary font-medium-1">{{ number_format($price->stock) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="hidden" name="prices[{{ $loop->index }}][stock_sync_enabled]" value="0">
                                                        <div class="custom-control custom-switch custom-switch-success">
                                                            <input type="checkbox" class="custom-control-input" id="sync-{{ $price->id }}" name="prices[{{ $loop->index }}][stock_sync_enabled]" value="1" {{ $price->stock_sync_enabled ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="sync-{{ $price->id }}"></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($price->stock_sync_error)
                                                            <span class="text-danger">{{ $price->stock_sync_error }}</span>
                                                        @elseif($price->stock_synced_at)
                                                            <span class="text-success">{{ jdate($price->stock_synced_at)->ago() }}</span>
                                                        @else
                                                            <span class="text-muted">هنوز همگام نشده</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            <button type="submit" class="btn btn-primary px-3">
                                <i class="feather icon-save"></i> ذخیره کدهای این محصول
                            </button>
                        </div>
                    </form>
                @empty
                    <section class="card">
                        <div class="card-body text-center text-muted">محصولی پیدا نشد.</div>
                    </section>
                @endforelse

                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('.copy-inventory-code');
        if (!button) return;

        var input = button.closest('.input-group').querySelector('.inventory-code-input');
        var value = input ? input.value : button.getAttribute('data-copy');

        navigator.clipboard.writeText(value).then(function () {
            var oldText = button.textContent;
            button.textContent = 'کپی شد';
            setTimeout(function () { button.textContent = oldText; }, 1200);
        });
    });

    document.addEventListener('input', function (event) {
        if (!event.target.classList.contains('inventory-code-input')) return;
        event.target.value = event.target.value
            .toUpperCase()
            .replace(/\s+/g, '-')
            .replace(/[^A-Z0-9._-]/g, '');
    });
</script>
@endpush

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
                                    <li class="breadcrumb-item active">کدهای انبار خودکار</li>
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

                <section class="card">
                    <div class="card-header">
                        <div>
                            <h4 class="card-title">کدهای خودکار اتصال به نرم‌افزار انبار</h4>
                            <p class="text-muted mb-0 mt-50">
                                این کدها توسط سایت ساخته می‌شوند و قابل ویرایش نیستند. کد ستون «کد قابل ثبت در انبار» را در نرم‌افزار انبارداری وارد کنید.
                            </p>
                        </div>
                        <a href="{{ route('admin.product-codes.export', request()->query()) }}" class="btn btn-outline-success">
                            <i class="feather icon-download"></i> خروجی CSV برای انبار
                        </a>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="alert alert-info">
                                <strong>محصول ساده:</strong> کد مادر محصول برای API استفاده می‌شود.
                                <br>
                                <strong>محصول متغیر:</strong> هر مدل کد مستقل خودش را دارد و همان کد برای API استفاده می‌شود.
                            </div>

                            <form method="GET" action="{{ route('admin.product-codes.index') }}" class="row align-items-end">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label for="product-code-search">جستجو در نام محصول، مدل یا کد انبار</label>
                                        <input id="product-code-search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="مثلاً ARY-P-00000125 یا iPhone 15 Pro Max">
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
                    @php
                        $isVariable = $product->usesVariantInventoryCodes();
                        $simplePrice = $product->prices->first();
                    @endphp

                    <section class="card mb-2 product-code-card">
                        <div class="card-header border-bottom">
                            <div class="d-flex align-items-center">
                                <img src="{{ $product->imageUrl() }}" alt="{{ $product->title }}" style="width:56px;height:56px;object-fit:cover;border-radius:6px" class="ml-1">
                                <div>
                                    <h5 class="mb-25">{{ $product->title }}</h5>
                                    <span class="badge {{ $isVariable ? 'badge-light-primary' : 'badge-light-success' }}">
                                        {{ $isVariable ? 'محصول متغیر' : 'محصول ساده' }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('admin.products.edit', ['product' => $product->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="feather icon-edit"></i> ویرایش محصول
                            </a>
                        </div>

                        <div class="card-content">
                            <div class="card-body">
                                <div class="row align-items-center mb-2">
                                    <div class="col-lg-7 col-md-8">
                                        <label>{{ $isVariable ? 'کد مادر محصول' : 'کد قابل ثبت در نرم‌افزار انبار' }}</label>
                                        <div class="input-group ltr">
                                            <input type="text" value="{{ $product->product_code }}" class="form-control inventory-code-input" readonly>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary copy-inventory-code" type="button" data-copy="{{ $product->product_code }}">
                                                    <i class="feather icon-copy"></i> کپی
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-4 mt-1 mt-md-0">
                                        @if($isVariable)
                                            <small class="text-muted">این کد فقط شناسه مادر است؛ برای انبار، کد مستقل هر مدل را از جدول پایین وارد کنید.</small>
                                        @else
                                            <div class="alert alert-success mb-0 py-1">
                                                همین کد را برای این محصول ساده در نرم‌افزار انبار وارد کنید.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($isVariable)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>مدل / تنوع</th>
                                                    <th style="min-width:280px">کد قابل ثبت در انبار</th>
                                                    <th>موجودی API</th>
                                                    <th>آخرین وضعیت همگام‌سازی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->prices as $price)
                                                    <tr>
                                                        <td><strong>{{ trim($price->getAttributesValue()) ?: 'تنوع بدون عنوان' }}</strong></td>
                                                        <td>
                                                            <div class="input-group ltr">
                                                                <input type="text" value="{{ $price->stock_code }}" class="form-control inventory-code-input" readonly>
                                                                <div class="input-group-append">
                                                                    <button class="btn btn-outline-secondary copy-inventory-code" type="button" data-copy="{{ $price->stock_code }}">
                                                                        <i class="feather icon-copy"></i> کپی
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-light-primary font-medium-1">{{ number_format($price->stock) }}</span>
                                                        </td>
                                                        <td>
                                                            @if($price->stock_sync_error)
                                                                <span class="text-danger">{{ $price->stock_sync_error }}</span>
                                                            @elseif($price->stock_synced_at)
                                                                <span class="text-success">آخرین دریافت: {{ jdate($price->stock_synced_at)->ago() }}</span>
                                                            @else
                                                                <span class="text-muted">هنوز از API دریافت نشده</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @elseif($simplePrice)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="border rounded p-1 text-center">
                                                <small class="text-muted d-block">موجودی دریافت‌شده از API</small>
                                                <strong class="font-medium-3">{{ number_format($simplePrice->stock) }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-md-8 mt-1 mt-md-0">
                                            <div class="border rounded p-1 h-100">
                                                @if($simplePrice->stock_sync_error)
                                                    <span class="text-danger">{{ $simplePrice->stock_sync_error }}</span>
                                                @elseif($simplePrice->stock_synced_at)
                                                    <span class="text-success">آخرین دریافت از API: {{ jdate($simplePrice->stock_synced_at)->ago() }}</span>
                                                @else
                                                    <span class="text-muted">این محصول هنوز از API انبار همگام نشده است.</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
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

        var value = button.getAttribute('data-copy') || '';
        var copyDone = function () {
            var oldHtml = button.innerHTML;
            button.textContent = 'کپی شد';
            setTimeout(function () { button.innerHTML = oldHtml; }, 1200);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(copyDone);
            return;
        }

        var temp = document.createElement('textarea');
        temp.value = value;
        temp.style.position = 'fixed';
        temp.style.opacity = '0';
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        temp.remove();
        copyDone();
    });
</script>
@endpush

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

                                <li class="breadcrumb-item">
                                    مدیریت
                                </li>

                                <li class="breadcrumb-item">
                                    کاربران
                                </li>

                                <li class="breadcrumb-item active">
                                    درخواست‌های عضویت
                                </li>

                            </ol>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="content-body">

            <section class="card">

                <div class="card-header">

                    <div>

                        <h4 class="card-title">
                            درخواست‌های عضویت
                        </h4>

                        <small class="text-muted">
                            درخواست‌های در انتظار بررسی:
                            <strong>{{ $pendingCount }}</strong>
                        </small>

                    </div>

                </div>

                <div class="card-content">

                    <div class="card-body">

                        <form method="GET" class="mb-2">

                            <div class="row">

                                <div class="col-lg-5 col-md-5 col-12 mb-1">

                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        value="{{ request('search') }}"
                                        placeholder="نام یا شماره موبایل"
                                    >

                                </div>

                                <div class="col-lg-4 col-md-4 col-12 mb-1">

                                    <select name="status" class="form-control">

                                        <option
                                            value="all"
                                            {{ $selectedStatus === 'all' ? 'selected' : '' }}
                                        >
                                            همه وضعیت‌ها
                                        </option>

                                        <option
                                            value="pending"
                                            {{ $selectedStatus === 'pending' ? 'selected' : '' }}
                                        >
                                            در حال بررسی
                                        </option>

                                        <option
                                            value="approved"
                                            {{ $selectedStatus === 'approved' ? 'selected' : '' }}
                                        >
                                            تأیید شده
                                        </option>

                                        <option
                                            value="rejected"
                                            {{ $selectedStatus === 'rejected' ? 'selected' : '' }}
                                        >
                                            رد شده
                                        </option>

                                    </select>

                                </div>

                                <div class="col-lg-3 col-md-3 col-12 mb-1">

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        جستجو
                                    </button>

                                    <a
                                        href="{{ route('admin.membership-requests.index') }}"
                                        class="btn btn-outline-secondary"
                                    >
                                        پاک کردن
                                    </a>

                                </div>

                            </div>

                        </form>

                        <div class="table-responsive">

                            <table class="table table-striped table-hover mb-0">

                                <thead>

                                    <tr>
                                        <th>#</th>
                                        <th>نام و نام خانوادگی</th>
                                        <th>موبایل</th>
                                        <th>استان</th>
                                        <th>شهر</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ</th>
                                        <th class="text-center">عملیات</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse($membershipRequests as $item)

                                        <tr>

                                            <td>{{ $item->id }}</td>

                                            <td>
                                                {{ $item->full_name }}
                                            </td>

                                            <td class="ltr text-right">
                                                {{ $item->mobile }}
                                            </td>

                                            <td>
                                                {{ $item->province?->name ?: '—' }}
                                            </td>

                                            <td>
                                                {{ $item->city?->name ?: '—' }}
                                            </td>

                                            <td>

                                                @if($item->isPending())

                                                    <span class="badge badge-warning">
                                                        در حال بررسی
                                                    </span>

                                                @elseif($item->isApproved())

                                                    <span class="badge badge-success">
                                                        تأیید شده
                                                    </span>

                                                @else

                                                    <span class="badge badge-danger">
                                                        رد شده
                                                    </span>

                                                @endif

                                            </td>

                                            <td>
                                                {{ $item->created_at }}
                                            </td>

                                            <td class="text-center">

                                                <a
                                                    href="{{ route('admin.membership-requests.show', $item) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    مشاهده
                                                </a>

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td
                                                colspan="8"
                                                class="text-center"
                                            >
                                                چیزی برای نمایش وجود ندارد.
                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                        @if($membershipRequests->hasPages())

                            <div class="mt-2">
                                {{ $membershipRequests->links() }}
                            </div>

                        @endif

                    </div>

                </div>

            </section>

        </div>

    </div>

</div>

@endsection

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

                        <h2 class="content-header-title float-left mb-0">
                            درخواست عضویت
                        </h2>

                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    مدیریت
                                </li>

                                <li class="breadcrumb-item">
                                    کاربران
                                </li>

                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.membership-requests.index') }}">
                                        درخواست‌های عضویت
                                    </a>
                                </li>

                                <li class="breadcrumb-item active">
                                    درخواست شماره {{ $membershipRequest->id }}
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
                    <h4 class="card-title">
                        مشخصات درخواست عضویت
                    </h4>

                    <div class="heading-elements">
                        <a
                            href="{{ route('admin.membership-requests.index') }}"
                            class="btn btn-outline-primary"
                        >
                            بازگشت
                        </a>
                    </div>
                </div>

                <div class="card-content">
                    <div class="card-body">

                        <div class="row">

                            <div class="col-lg-6 col-md-6 col-12">
                                <table class="table">
                                    <tbody>

                                        <tr>
                                            <td class="font-weight-bold">
                                                نام
                                            </td>
                                            <td>
                                                {{ $membershipRequest->first_name }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="font-weight-bold">
                                                نام خانوادگی
                                            </td>
                                            <td>
                                                {{ $membershipRequest->last_name }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="font-weight-bold">
                                                شماره موبایل
                                            </td>
                                            <td class="ltr text-right">
                                                {{ $membershipRequest->mobile }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="font-weight-bold">
                                                استان
                                            </td>
                                            <td>
                                                {{ $membershipRequest->province?->name ?: '—' }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="font-weight-bold">
                                                شهر
                                            </td>
                                            <td>
                                                {{ $membershipRequest->city?->name ?: '—' }}
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="col-lg-6 col-md-6 col-12">
                                <table class="table">
                                    <tbody>

                                        <tr>
                                            <td class="font-weight-bold">
                                                فروشگاه / مجموعه
                                            </td>
                                            <td>
                                                {{ $membershipRequest->business_name ?: '—' }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="font-weight-bold">
                                                وضعیت
                                            </td>
                                            <td>
                                                @if($membershipRequest->isPending())
                                                    <span class="badge badge-warning">
                                                        در حال بررسی
                                                    </span>
                                                @elseif($membershipRequest->isApproved())
                                                    <span class="badge badge-success">
                                                        تأیید شده
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                        رد شده
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="font-weight-bold">
                                                تاریخ ثبت درخواست
                                            </td>
                                            <td>
                                                {{ $membershipRequest->created_at }}
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                        </div>

                        @php
                            $documentImageUrl = $membershipRequest->documentImageUrl();
                        @endphp

                        @if($documentImageUrl)

                            <hr>

                            <div class="mb-2">
                                <strong>تصویر جواز کسب / درب فروشگاه:</strong>
                            </div>

                            <div>
                                <a
                                    href="{{ $documentImageUrl }}"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    <img
                                        src="{{ $documentImageUrl }}"
                                        alt="مدرک عضویت"
                                        style="max-width: 420px; max-height: 420px; object-fit: contain;"
                                        class="img-fluid border rounded p-1"
                                    >
                                </a>
                            </div>

                        @elseif($membershipRequest->document_image)

                            <hr>

                            <div class="alert alert-warning mb-0">
                                مسیر تصویر ثبت شده است اما فایل در فضای ذخیره‌سازی پیدا نشد.
                            </div>

                        @endif

                        @if($membershipRequest->reviewed_at)

                            <hr>

                            <div class="row">

                                <div class="col-md-6">
                                    <strong>تاریخ بررسی:</strong>
                                    {{ $membershipRequest->reviewed_at }}
                                </div>

                                <div class="col-md-6">
                                    <strong>بررسی‌کننده:</strong>
                                    {{ $membershipRequest->reviewer?->fullname ?: '—' }}
                                </div>

                            </div>

                        @endif

                        @if($membershipRequest->isRejected())

                            <hr>

                            <div class="alert alert-danger">
                                <strong>دلیل رد درخواست:</strong>

                                <div class="mt-1">
                                    {{ $membershipRequest->reject_reason }}
                                </div>
                            </div>

                        @endif

                        @if($membershipRequest->approvedUser)

                            <hr>

                            <div class="alert alert-success">
                                حساب کاربری ایجاد شده:

                                <strong>
                                    #{{ $membershipRequest->approvedUser->id }}
                                    -
                                    {{ $membershipRequest->approvedUser->username }}
                                </strong>
                            </div>

                        @endif

                    </div>
                </div>
            </section>


            @if($membershipRequest->isPending())

                <div class="row match-height">

                    <div class="col-lg-6 col-md-6 col-12">

                        <section class="card">

                            <div class="card-header">
                                <h4 class="card-title">
                                    تأیید درخواست
                                </h4>
                            </div>

                            <div class="card-content">
                                <div class="card-body">

                                    <p>
                                        با تأیید درخواست، حساب کاربری این همکار در سایت ایجاد خواهد شد.
                                    </p>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.membership-requests.approve', $membershipRequest) }}"
                                        onsubmit="return confirm('این درخواست تأیید شود و حساب کاربری ساخته شود؟')"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-success btn-block"
                                        >
                                            تأیید عضویت
                                        </button>

                                    </form>

                                </div>
                            </div>

                        </section>

                    </div>


                    <div class="col-lg-6 col-md-6 col-12">

                        <section class="card">

                            <div class="card-header">
                                <h4 class="card-title">
                                    رد درخواست
                                </h4>
                            </div>

                            <div class="card-content">
                                <div class="card-body">

                                    <form
                                        method="POST"
                                        action="{{ route('admin.membership-requests.reject', $membershipRequest) }}"
                                    >
                                        @csrf

                                        <div class="form-group">

                                            <label for="reject_reason">
                                                دلیل رد درخواست
                                            </label>

                                            <textarea
                                                id="reject_reason"
                                                name="reject_reason"
                                                class="form-control"
                                                rows="4"
                                                maxlength="1000"
                                                required
                                            >{{ old('reject_reason') }}</textarea>

                                            @error('reject_reason')
                                                <small class="text-danger">
                                                    {{ $message }}
                                                </small>
                                            @enderror

                                        </div>

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-block"
                                            onclick="return confirm('این درخواست رد شود؟')"
                                        >
                                            رد درخواست
                                        </button>

                                    </form>

                                </div>
                            </div>

                        </section>

                    </div>

                </div>

            @endif

        </div>
    </div>
</div>

@endsection

@extends('front::auth.layouts.master', ['title' => 'وضعیت درخواست عضویت'])

@section('content')

<main class="main-content dt-sl mt-4 mb-4">

    <div class="container main-container">

        <div class="row">

            <div class="col-xl-5 col-lg-6 col-md-8 col-12 mx-auto">

                <div class="form-ui dt-sl dt-sn pt-4 px-3 pb-4 text-center">

                    <div class="section-title title-wide mb-3 no-after-title-wide">

                        <h2 class="font-weight-bold">
                            وضعیت درخواست عضویت
                        </h2>

                    </div>

                    @if(session('membership_submitted'))

                        <div class="alert alert-success">
                            درخواست عضویت شما با موفقیت ثبت شد و در حال بررسی است. نتیجه بررسی حداکثر ظرف ۲۴ ساعت آینده به شما اعلام خواهد شد.
                        </div>

                    @endif

                    @if($membership->isPending())

                        <div class="alert alert-warning">

                            <strong>
                                درخواست عضویت شما ثبت شده و در انتظار تأیید حساب کاربری است. نتیجه بررسی حداکثر ظرف ۲۴ ساعت آینده به شما اعلام خواهد شد.
                            </strong>

                        </div>

                    @elseif($membership->isApproved())

                        <div class="alert alert-success">

                            <strong>
                                درخواست عضویت شما تأیید شده است.
                            </strong>

                            <div class="mt-2">
                                حساب کاربری شما فعال شده و اکنون می‌توانید وارد فروشگاه شوید.
                            </div>

                        </div>

                        <a
                            href="{{ route('login') }}"
                            class="btn-primary-cm btn-with-icon d-block w-100 mt-3"
                        >
                            ورود به حساب کاربری
                        </a>

                    @elseif($membership->isRejected())

                        <div class="alert alert-danger">

                            <strong>
                                درخواست عضویت شما تأیید نشد.
                            </strong>

                            @if($membership->reject_reason)

                                <div class="mt-3">
                                    <strong>دلیل رد:</strong>
                                    {{ $membership->reject_reason }}
                                </div>

                            @endif

                        </div>

                        <a
                            href="{{ route('membership.request') }}"
                            class="btn-primary-cm btn-with-icon d-block w-100 mt-3"
                        >
                            اصلاح و ارسال مجدد
                        </a>

                    @endif

                    <div class="mt-4 text-muted">

                        شماره درخواست:

                        <strong>
                            {{ $membership->id }}
                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>

@endsection

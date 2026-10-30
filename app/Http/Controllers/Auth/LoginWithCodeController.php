<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MembershipRequest;
use App\Models\OneTimeCode;
use App\Models\User;
use App\Support\AuthRedirect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginWithCodeController extends Controller
{
    private const OTP_EXPIRE_MINUTES = 5;
    private const OTP_RESEND_SECONDS = 120;
    private const OTP_MAX_VERIFY_ATTEMPTS = 5;
    private const OTP_VERIFY_LOCK_SECONDS = 300;

    public function create(Request $request)
    {
        $view = config('front.pages.login-with-code');

        if (!$view) {
            abort(404);
        }

        // Preserve safe local intended URL, for example product → login → product.
        $candidates = [
            $request->query('redirect'),
            $request->session()->pull('url.intended'),
            $request->session()->pull(AuthRedirect::CUSTOMER_INTENDED),
        ];

        foreach ($candidates as $candidate) {
            if ($redirect = AuthRedirect::safeStorefrontUrl($request, $candidate)) {
                $request->session()->put(AuthRedirect::CUSTOMER_INTENDED, $redirect);
                break;
            }
        }

        return view($view);
    }

    public function store(Request $request)
    {
        $mobile = $this->normalizeMobile((string) $request->input('mobile'));

        $request->merge([
            'mobile' => $mobile,
        ]);

        $request->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
        ], [
            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل وارد شده معتبر نیست.',
        ]);

        $user = User::where('username', $mobile)->first();

        /*
         * Existing customers are allowed to login with OTP even if they do
         * not have a membership_requests record.
         */
        if (!$user) {
            $membership = MembershipRequest::query()
                ->where('mobile', $mobile)
                ->latest('id')
                ->first();

            if (!$membership) {
                $request->session()->forget('membership_request_id');
                $request->session()->put('membership_mobile', $mobile);

                return response()->json([
                    'status' => 'registration_required',
                    'redirect' => route('membership.request', [
                        'mobile' => $mobile,
                    ]),
                ]);
            }

            $request->session()->put([
                'membership_request_id' => $membership->id,
                'membership_mobile' => $mobile,
            ]);

            if ($membership->status === MembershipRequest::STATUS_PENDING) {
                return response()->json([
                    'status' => 'pending',
                    'redirect' => route('membership.status'),
                ]);
            }

            if ($membership->status === MembershipRequest::STATUS_REJECTED) {
                return response()->json([
                    'status' => 'rejected',
                    'redirect' => route('membership.status'),
                ]);
            }

            /*
             * An approved request must have created its User.
             * Do not silently create users here; approval remains an Admin action.
             */
            logger()->warning('Approved membership request has no matching user.', [
                'membership_request_id' => $membership->id,
                'mobile' => $mobile,
            ]);

            return response()->json([
                'message' => 'درخواست شما تأیید شده اما حساب کاربری هنوز ایجاد نشده است. لطفاً با پشتیبانی تماس بگیرید.',
            ], 409);
        }

        $request->session()->forget([
            'membership_mobile',
            'membership_request_id',
        ]);

        $this->ensureCustomerIsActive($user, $request, 'mobile');

        $sendKey = $this->sendRateLimitKey($mobile, $request->ip());

        if (RateLimiter::tooManyAttempts($sendKey, 5)) {
            $seconds = RateLimiter::availableIn($sendKey);

            throw ValidationException::withMessages([
                'mobile' => "تعداد درخواست کد بیش از حد مجاز است. لطفاً {$seconds} ثانیه دیگر تلاش کنید.",
            ]);
        }

        $latestCode = OneTimeCode::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $shouldSend = true;

        if ($latestCode) {
            $age = $latestCode->created_at->diffInSeconds(now());

            if ($age < self::OTP_RESEND_SECONDS) {
                $shouldSend = false;
            }
        }

        if ($shouldSend) {
            $provider = option('sms_panel_provider', 'ippanel');

            if ($provider === 'melipayamak') {
                if (
                    trim((string) option('MELIPAYAMAK_PANEL_USERNAME')) === '' ||
                    trim((string) option('MELIPAYAMAK_PANEL_PASSWORD')) === '' ||
                    trim((string) option('user_verify_pattern_code_melipayamak')) === ''
                ) {
                    return response()->json([
                        'message' => 'تنظیمات ملی پیامک کامل نشده است.'
                    ], 503);
                }

                if (!extension_loaded('soap')) {
                    return response()->json([
                        'message' => 'افزونه SOAP روی سرور فعال نیست و ارسال ملی پیامک فعلاً امکان‌پذیر نیست.'
                    ], 503);
                }
            }

            try {
                PasswordResetLinkController::sendCode($user);
            } catch (\Throwable $exception) {
                OneTimeCode::where('user_id', $user->id)->delete();
                report($exception);

                return response()->json([
                    'message' => 'ارسال پیامک با خطا مواجه شد. لطفاً دوباره تلاش کنید.'
                ], 502);
            }

            RateLimiter::hit(
                $sendKey,
                10 * 60
            );
        }

        return response()->json([
            'status' => 'otp_sent',
            'redirect' => route('one-time-login', [
                'type' => 'login',
                'mobile' => $mobile,
            ]),
        ]);
    }

    public function confirm(Request $request)
    {
        $mobile = $this->normalizeMobile((string) $request->input('mobile'));

        $request->merge([
            'mobile' => $mobile,
        ]);

        $request->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/', 'exists:users,username'],
            'verify_code' => ['required', 'digits:5'],
        ], [
            'mobile.regex' => 'شماره موبایل معتبر نیست.',
            'mobile.exists' => 'حساب کاربری یافت نشد.',
            'verify_code.required' => 'کد تأیید را وارد کنید.',
            'verify_code.digits' => 'کد تأیید باید ۵ رقمی باشد.',
        ]);

        $user = User::where('username', $mobile)->firstOrFail();

        $this->ensureCustomerIsActive($user, $request, 'verify_code');

        $verifyKey = $this->verifyRateLimitKey(
            $mobile,
            $request->ip()
        );

        if (
            RateLimiter::tooManyAttempts(
                $verifyKey,
                self::OTP_MAX_VERIFY_ATTEMPTS
            )
        ) {
            $seconds = RateLimiter::availableIn($verifyKey);

            throw ValidationException::withMessages([
                'verify_code' =>
                    "تعداد تلاش ناموفق بیش از حد مجاز است. لطفاً {$seconds} ثانیه دیگر تلاش کنید.",
            ]);
        }

        $otp = OneTimeCode::where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (!$otp) {
            RateLimiter::hit(
                $verifyKey,
                self::OTP_VERIFY_LOCK_SECONDS
            );

            throw ValidationException::withMessages([
                'verify_code' =>
                    'کد تأییدی برای این شماره وجود ندارد. لطفاً کد جدید دریافت کنید.',
            ]);
        }

        $expiresAt = Carbon::parse($otp->created_at)
            ->addMinutes(self::OTP_EXPIRE_MINUTES);

        if (now()->greaterThan($expiresAt)) {
            $otp->delete();

            RateLimiter::hit(
                $verifyKey,
                self::OTP_VERIFY_LOCK_SECONDS
            );

            throw ValidationException::withMessages([
                'verify_code' =>
                    'کد تأیید منقضی شده است. لطفاً کد جدید دریافت کنید.',
            ]);
        }

        $valid = hash_equals(
            (string) $otp->code,
            (string) $request->input('verify_code')
        );

        if (!$valid) {
            RateLimiter::hit(
                $verifyKey,
                self::OTP_VERIFY_LOCK_SECONDS
            );

            throw ValidationException::withMessages([
                'verify_code' =>
                    'کد تأیید وارد شده صحیح نیست.',
            ]);
        }

        $user->refresh();
        $this->ensureCustomerIsActive($user, $request, 'verify_code');

        // The OTP itself proves ownership of the mobile number.
        if ($user->level === 'user' && !$user->verified_at) {
            $user->forceFill([
                'verified_at' => now(),
            ])->save();
        }

        Auth::loginUsingId($user->id, true);

        $request->session()->regenerate();
        $request->session()->put(
            'auth.password_confirmed_at',
            time()
        );

        // Prevent OTP reuse.
        OneTimeCode::where('user_id', $user->id)->delete();

        RateLimiter::clear($verifyKey);
        RateLimiter::clear(
            $this->sendRateLimitKey($mobile, $request->ip())
        );

        $redirect = AuthRedirect::pullStorefrontDestination($request);

        return response()->json([
            'status' => 'success',
            'redirect' => $redirect,
        ]);
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = trim($mobile);

        $mobile = strtr($mobile, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',

            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);

        $mobile = preg_replace('/[\s\-\(\)]+/', '', $mobile);

        if (str_starts_with($mobile, '+98')) {
            $mobile = '0' . substr($mobile, 3);
        } elseif (str_starts_with($mobile, '0098')) {
            $mobile = '0' . substr($mobile, 4);
        } elseif (
            str_starts_with($mobile, '98')
            && strlen($mobile) === 12
        ) {
            $mobile = '0' . substr($mobile, 2);
        } elseif (
            str_starts_with($mobile, '9')
            && strlen($mobile) === 10
        ) {
            $mobile = '0' . $mobile;
        }

        return $mobile;
    }

    private function sendRateLimitKey(string $mobile, ?string $ip): string
    {
        return 'customer-otp-send:' . $mobile . ':' . ($ip ?: 'unknown');
    }

    private function verifyRateLimitKey(string $mobile, ?string $ip): string
    {
        return 'customer-otp-verify:' . $mobile . ':' . ($ip ?: 'unknown');
    }

    private function ensureCustomerIsActive(
        User $user,
        Request $request,
        string $field
    ): void {
        if ($user->level !== 'user' || $user->is_active !== false) {
            return;
        }

        OneTimeCode::where('user_id', $user->id)->delete();
        RateLimiter::clear(
            $this->sendRateLimitKey($user->username, $request->ip())
        );
        RateLimiter::clear(
            $this->verifyRateLimitKey($user->username, $request->ip())
        );

        throw ValidationException::withMessages([
            $field => User::INACTIVE_CUSTOMER_MESSAGE,
        ]);
    }
}

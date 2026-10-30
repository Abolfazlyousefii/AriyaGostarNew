<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OneTimeCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OneTimeLoginController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|exists:users,username'
        ]);

        $view = config('front.pages.one-time-login');

        if (!$view || $validator->fails()) {
            abort(404);
        }

        $user = User::where('username', $request->mobile)->first();

        if ($user->level === 'user' && $user->is_active === false) {
            OneTimeCode::where('user_id', $user->id)->delete();

            return redirect()
                ->route('login')
                ->with(
                    'status',
                    User::INACTIVE_CUSTOMER_MESSAGE
                );
        }

        $verify_code = OneTimeCode::where('user_id', $user->id)->latest()->first();

        if (!$verify_code) {
            return redirect()
                ->route('login', ['mobile' => $request->mobile])
                ->withErrors([
                    'mobile' => 'کد ورود یافت نشد. لطفاً دوباره درخواست کد کنید.'
                ]);
        }

        $resend_time = $verify_code->created_at->addSeconds(120)->timestamp;

        return view($view, compact('resend_time', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mobile' => 'required|exists:users,username',
        ]);

        $user = User::where('username', $request->mobile)->first();
        $this->ensureCustomerIsActive($user);

        $time = Carbon::now()->subMinutes(15);

        $request->validate([
            'verify_code'     => [
                'required',
                Rule::exists('one_time_codes', 'code')->where(function ($query) use ($user, $time) {
                    $query->where('user_id', $user->id)->where('created_at', '>=', $time);
                }),
            ]
        ], [
            'verify_code.exists' => 'کد وارد شده اشتباه است'
        ]);

        $user->update([
            'force_to_password_change' => true,
        ]);

        $user->refresh();
        $this->ensureCustomerIsActive($user);

        Auth::loginUsingId($user->id, true);
        OneTimeCode::where('user_id', $user->id)->delete();

        return response('success');
    }

    private function ensureCustomerIsActive(User $user): void
    {
        if ($user->level !== 'user' || $user->is_active !== false) {
            return;
        }

        OneTimeCode::where('user_id', $user->id)->delete();

        throw ValidationException::withMessages([
            'mobile' => User::INACTIVE_CUSTOMER_MESSAGE,
        ]);
    }
}

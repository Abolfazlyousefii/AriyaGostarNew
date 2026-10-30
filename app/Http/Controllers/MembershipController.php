<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\MembershipRequest;
use App\Models\Province;
use App\Models\User;
use App\Support\ImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MembershipController extends Controller
{
    public function create(Request $request)
    {
        $mobile = $this->normalizeMobile(
            (string) session('membership_mobile', '')
        );

        if (!$mobile) {
            return redirect()->route('login');
        }

        if (User::where('username', $mobile)->exists()) {
            return redirect()->route('login');
        }

        session([
            'membership_mobile' => $mobile,
        ]);

        $latestMembership = MembershipRequest::query()
            ->where('mobile', $mobile)
            ->latest('id')
            ->first();

        if ($latestMembership && $latestMembership->isPending()) {
            session([
                'membership_request_id' => $latestMembership->id,
            ]);

            return redirect()->route('membership.status');
        }

        $previousMembership = $latestMembership && $latestMembership->isRejected()
            ? $latestMembership
            : null;

        $provinces = Province::query()
            ->active()
            ->orderBy('ordering')
            ->orderBy('name')
            ->get(['id', 'name']);

        $cities = City::query()
            ->active()
            ->whereIn('province_id', $provinces->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'province_id', 'name'])
            ->groupBy('province_id');

        return view('front::membership.request', compact(
            'mobile',
            'provinces',
            'cities',
            'previousMembership'
        ));
    }

    public function store(Request $request)
    {
        $sessionMobile = $this->normalizeMobile(
            (string) session('membership_mobile', '')
        );

        if (!$sessionMobile) {
            return redirect()->route('login');
        }

        $postedMobile = $this->normalizeMobile(
            (string) $request->input('mobile')
        );

        if ($postedMobile !== $sessionMobile) {
            throw ValidationException::withMessages([
                'mobile' => 'شماره موبایل قابل تغییر نیست. لطفاً فرآیند عضویت را از صفحه ورود آغاز کنید.',
            ]);
        }

        if (User::where('username', $sessionMobile)->exists()) {
            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'این شماره قبلاً عضو سایت است. لطفاً وارد شوید.'
                );
        }

        $previousMembership = MembershipRequest::query()
            ->where('mobile', $sessionMobile)
            ->rejected()
            ->latest('id')
            ->first();

        $hasPreviousDocument = $previousMembership
            && !empty($previousMembership->document_image);

        $documentRules = [
            $hasPreviousDocument ? 'nullable' : 'required',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:5120',
        ];

        $validator = Validator::make(
            [
                'full_name' => trim(
                    preg_replace(
                        '/\s+/u',
                        ' ',
                        (string) $request->input('full_name')
                    )
                ),
                'mobile' => $sessionMobile,
                'province_id' => $request->input('province_id'),
                'city_id' => $request->input('city_id'),
                'document_image' => $request->file('document_image'),
            ],
            [
                'full_name' => [
                    'required',
                    'string',
                    'max:191',
                    'regex:/^\S+(?:\s+\S+)+$/u',
                ],
                'mobile' => [
                    'required',
                    'regex:/^09[0-9]{9}$/',
                ],
                'province_id' => [
                    'required',
                    'integer',
                    'exists:provinces,id',
                ],
                'city_id' => [
                    'required',
                    'integer',
                    'exists:cities,id',
                ],
                'document_image' => $documentRules,
            ],
            [
                'full_name.required' =>
                    'نام و نام خانوادگی را وارد کنید.',
                'full_name.regex' =>
                    'نام و نام خانوادگی را کامل وارد کنید.',
                'mobile.required' =>
                    'شماره موبایل را وارد کنید.',
                'mobile.regex' =>
                    'شماره موبایل معتبر نیست.',
                'province_id.required' =>
                    'استان را انتخاب کنید.',
                'province_id.exists' =>
                    'استان انتخاب‌شده معتبر نیست.',
                'city_id.required' =>
                    'شهر را انتخاب کنید.',
                'city_id.exists' =>
                    'شهر انتخاب‌شده معتبر نیست.',
                'document_image.required' =>
                    'تصویر جواز کسب یا درب فروشگاه را بارگذاری کنید.',
                'document_image.image' =>
                    'فایل انتخاب‌شده باید تصویر باشد.',
                'document_image.mimes' =>
                    'فرمت تصویر باید JPG، JPEG، PNG یا WEBP باشد.',
                'document_image.max' =>
                    'حجم تصویر نباید بیشتر از ۵ مگابایت باشد.',
            ]
        );

        $validator->validate();

        $cityIsValid = City::query()
            ->active()
            ->whereKey($request->input('city_id'))
            ->where('province_id', $request->input('province_id'))
            ->exists();

        if (!$cityIsValid) {
            throw ValidationException::withMessages([
                'city_id' => 'شهر انتخاب‌شده متعلق به استان انتخاب‌شده نیست.',
            ]);
        }

        $existingPending = MembershipRequest::query()
            ->where('mobile', $sessionMobile)
            ->pending()
            ->latest('id')
            ->first();

        if ($existingPending) {
            session([
                'membership_mobile' => $existingPending->mobile,
                'membership_request_id' => $existingPending->id,
            ]);

            return redirect()->route('membership.status');
        }

        [$firstName, $lastName] = $this->splitFullName(
            trim(
                preg_replace(
                    '/\s+/u',
                    ' ',
                    (string) $request->input('full_name')
                )
            )
        );

        $documentPath = $previousMembership?->document_image;
        $newDocumentPath = null;

        if ($request->hasFile('document_image')) {
            $newDocumentPath = $request
                ->file('document_image')
                    ->store('membership-documents', 'arvan');

            $documentPath = $newDocumentPath;
        }

        try {
            $membership = DB::transaction(
                function () use (
                    $sessionMobile,
                    $firstName,
                    $lastName,
                    $request,
                    $documentPath
                ) {
                    $existing = MembershipRequest::query()
                        ->where('mobile', $sessionMobile)
                        ->pending()
                        ->lockForUpdate()
                        ->latest('id')
                        ->first();

                    if ($existing) {
                        return $existing;
                    }

                    return MembershipRequest::create([
                        'mobile' => $sessionMobile,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'business_name' => null,
                        'province_id' => $request->input('province_id'),
                        'city_id' => $request->input('city_id'),
                        'document_image' => $documentPath,
                        'status' => MembershipRequest::STATUS_PENDING,
                    ]);
                }
            );
        } catch (\Throwable $e) {
            if ($newDocumentPath) {
                Storage::disk('uploads')->delete($newDocumentPath);
            }

            throw $e;
        }

        session([
            'membership_mobile' => $membership->mobile,
            'membership_request_id' => $membership->id,
        ]);

        return redirect()
            ->route('membership.status')
            ->with('membership_submitted', true);
    }

    public function status(Request $request)
    {
        $mobile = $this->normalizeMobile(
            (string) session('membership_mobile', '')
        );

        $requestId = session('membership_request_id');

        if (!$mobile) {
            return redirect()->route('login');
        }

        $query = MembershipRequest::query()
            ->where('mobile', $mobile);

        if ($requestId) {
            $query->where('id', $requestId);
        }

        $membership = $query
            ->latest('id')
            ->first();

        if (!$membership) {
            session()->forget([
                'membership_mobile',
                'membership_request_id',
            ]);

            return redirect()->route('login');
        }

        return view(
            'front::membership.status',
            compact('membership')
        );
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = convertPersianToEnglish($mobile);
        $mobile = preg_replace('/[^0-9]/', '', $mobile) ?: '';

        if (
            strlen($mobile) === 12
            && str_starts_with($mobile, '98')
        ) {
            $mobile = '0' . substr($mobile, 2);
        }

        if (
            strlen($mobile) === 10
            && str_starts_with($mobile, '9')
        ) {
            $mobile = '0' . $mobile;
        }

        return $mobile;
    }

    private function splitFullName(string $fullName): array
    {
        $parts = preg_split(
            '/\s+/u',
            trim($fullName),
            2
        );

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }
}

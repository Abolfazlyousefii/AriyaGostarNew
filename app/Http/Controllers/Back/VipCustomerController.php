<?php
namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class VipCustomerController extends Controller
{
    private function authorizeManager(): void
    {
        abort_unless(auth()->check() && auth()->user()->can('users.update'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeManager();
        $q = trim((string) $request->query('q', ''));
        $q = mb_substr($q, 0, 100);

        $matches = collect();
        if ($q !== '') {
            // Protect query from LIKE wildcard abuse; search is scoped to Site users.
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $matches = User::query()->without('customer')->where('level', 'user')
                ->where(function ($query) use ($like) {
                    $query->where('username', 'like', $like)
                        ->orWhere('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                    if (Schema::hasColumn('users', 'mobile')) {
                        $query->orWhere('mobile', 'like', $like);
                    }
                })->orderBy('id', 'desc')->limit(20)->get(['id', 'first_name', 'last_name', 'username', 'email']);
        }

        $memberships = DB::table('vip_customer_memberships as vip')
            ->join('users as u', 'u.id', '=', 'vip.user_id')
            ->leftJoin('vip_customer_badges as badges', 'badges.user_id', '=', 'vip.user_id')
            ->where('vip.active', 1)
            ->select('vip.user_id', 'vip.created_at', 'u.first_name', 'u.last_name', 'u.username', 'badges.icon_type', 'badges.emoji')
            ->orderBy('vip.updated_at', 'desc')->paginate(25)->withQueryString();

        $percent = DB::table('vip_discount_settings')->where('id', 1)->value('percent') ?? 0;
        return view('back.vip-customers.index', compact('matches', 'memberships', 'percent', 'q'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager();
        $data = $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $user = User::findOrFail($data['user_id']);
        if ((string) $user->level !== 'user') {
            return back()->withErrors(['user_id' => 'فقط حساب مشتری را می‌توان VIP کرد.']);
        }

        DB::table('vip_customer_memberships')->updateOrInsert(
            ['user_id' => $user->id],
            ['active' => 1, 'updated_at' => now()]
        );
        return redirect()->route('admin.vip-customers.index')->with('success', 'مشتری به لیست VIP اضافه شد.');
    }

    public function updatePercent(Request $request)
    {
        $this->authorizeManager();
        $data = $request->validate(['percent' => 'required|numeric|between:0,100|regex:/^\d{1,3}(\.\d{1,2})?$/']);
        $rate = round((float) $data['percent'], 2);
        DB::table('vip_discount_settings')->where('id', 1)->update(['percent' => $rate, 'updated_at' => now()]);
        return redirect()->route('admin.vip-customers.index')->with('success', 'درصد عمومی VIP ذخیره شد.');
    }

    public function updateBadge(Request $request, User $user)
    {
        $this->authorizeManager();

        $data = $request->validate([
            'icon_type' => ['required', 'in:premium_star,emoji'],
            'emoji' => ['nullable', 'string', 'max:32'],
        ]);

        $isVip = DB::table('vip_customer_memberships')
            ->where('user_id', $user->id)->where('active', 1)->exists();
        if (!$isVip) {
            return back()->withErrors(['icon_type' => 'این مشتری عضویت VIP فعال ندارد.']);
        }

        $type = $data['icon_type'];
        $emoji = trim((string) ($data['emoji'] ?? ''));
        if ($type === 'emoji' && $emoji === '') {
            return back()->withErrors(['emoji' => 'لطفاً یک ایموجی وارد کنید.']);
        }
        if ($type !== 'emoji') {
            $emoji = null;
        }

        DB::table('vip_customer_badges')->updateOrInsert(
            ['user_id' => $user->id],
            ['icon_type' => $type, 'emoji' => $emoji, 'updated_at' => now()]
        );

        return redirect()->route('admin.vip-customers.index')
            ->with('success', 'آیکن VIP مشتری ذخیره شد.');
    }

    public function destroy(User $user)
    {
        $this->authorizeManager();
        DB::table('vip_customer_memberships')->where('user_id', $user->id)
            ->update(['active' => 0, 'updated_at' => now()]);
        return redirect()->route('admin.vip-customers.index')->with('success', 'عضویت VIP غیرفعال شد.');
    }
}

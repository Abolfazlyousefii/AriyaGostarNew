@extends('back.layouts.master')

@section('content')
<div class="app-content content"><div class="content-overlay"></div><div class="header-navbar-shadow"></div>
 <div class="content-wrapper"><div class="content-header row"><div class="col-12 mb-2"><h3>مدیریت مشتریان VIP</h3></div></div>
 <div class="content-body">
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
  <div class="card"><div class="card-body">
   <h4>درصد تخفیف عمومی VIP</h4>
   <p class="text-muted">این درصد روی مبلغ کالاها پس از تخفیف کالا و کد تخفیف اعمال می‌شود؛ هزینه ارسال مشمول نیست. درصد ۰ قابلیت را عملاً غیرفعال می‌کند.</p>
   <form method="POST" action="{{ route('admin.vip-customers.percent') }}" class="form-inline">@csrf
    <input class="form-control mx-2" name="percent" type="number" min="0" max="100" step="0.01" value="{{ old('percent', $percent) }}" required aria-label="درصد تخفیف VIP">
    <button type="submit" class="btn btn-primary">ذخیره درصد</button>
   </form>
  </div></div>
  <div class="card"><div class="card-body"><h4>جستجو و افزودن مشتری</h4>
   <form method="GET" action="{{ route('admin.vip-customers.index') }}" class="mb-3 form-inline">
    <input name="q" class="form-control mx-2" style="min-width:270px" maxlength="100" value="{{ $q }}" placeholder="نام، نام خانوادگی، موبایل یا نام کاربری" aria-label="جستجوی کاربر">
    <button class="btn btn-outline-primary" type="submit">جستجو</button>
   </form>
   @if($q !== '')
    <div class="table-responsive"><table class="table table-striped"><thead><tr><th>شناسه</th><th>نام</th><th>نام کاربری / شماره</th><th>عملیات</th></tr></thead><tbody>
    @forelse($matches as $candidate)
     <tr><td>{{ $candidate->id }}</td><td>{{ $candidate->first_name }} {{ $candidate->last_name }}</td><td>{{ $candidate->username }}</td>
     <td><form method="POST" action="{{ route('admin.vip-customers.store') }}">@csrf<input type="hidden" name="user_id" value="{{ $candidate->id }}"><button class="btn btn-sm btn-success">افزودن به VIP</button></form></td></tr>
    @empty<tr><td colspan="4">مشتری پیدا نشد.</td></tr>@endforelse
    </tbody></table></div>
   @endif
  </div></div>
  <style>
    @keyframes vipBadgeFloat { 0%,100% { transform: translateY(0) rotate(-3deg) scale(1); } 50% { transform:translateY(-2px) rotate(3deg) scale(1.08); } }
    @media (prefers-reduced-motion: reduce) { .vip-badge-preview img { animation:none!important; } }
  </style>
  <div class="card"><div class="card-body"><h4>فهرست مشتریان VIP فعال</h4>
   <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>شناسه</th><th>نام مشتری</th><th>نام کاربری</th><th>آیکن VIP</th><th>عملیات</th></tr></thead><tbody>
   @forelse($memberships as $member)
    <tr><td>{{ $member->user_id }}</td><td>{{ $member->first_name }} {{ $member->last_name }}</td><td>{{ $member->username }}</td>
    <td>
      @php($vipIconType = $member->icon_type ?: 'premium_star')
      @php($vipEmoji = trim((string) ($member->emoji ?? '')))
      <div class="d-flex align-items-center mb-1" style="gap:8px">
        @if($vipIconType === 'emoji' && $vipEmoji !== '')
          <span style="font-size:26px">{{ $vipEmoji }}</span>
        @else
          <img src="{{ asset('assets/ariya/telegram-premium-star.svg') }}" width="30" height="30" alt="ستاره پریمیوم" style="animation:vipBadgeFloat 2.6s ease-in-out infinite">
        @endif
      </div>
      <form method="POST" action="{{ route('admin.vip-customers.badge', ['user' => $member->user_id]) }}" class="d-flex flex-wrap align-items-center" style="gap:6px">
        @csrf
        <select name="icon_type" class="form-control form-control-sm" style="min-width:135px">
          <option value="premium_star" {{ $vipIconType === 'premium_star' ? 'selected' : '' }}>ستاره متحرک</option>
          <option value="emoji" {{ $vipIconType === 'emoji' ? 'selected' : '' }}>ایموجی دلخواه</option>
        </select>
        <input name="emoji" type="text" maxlength="32" value="{{ $vipEmoji }}" placeholder="👑 ⭐ 💎" class="form-control form-control-sm" style="width:105px" aria-label="ایموجی VIP">
        <button class="btn btn-sm btn-primary" type="submit">ذخیره</button>
      </form>
    </td>
    <td><form method="POST" action="{{ route('admin.vip-customers.destroy', ['user' => $member->user_id]) }}" onsubmit="return confirm('عضویت VIP این مشتری غیرفعال شود؟')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">حذف از VIP</button></form></td></tr>
   @empty<tr><td colspan="5">هنوز مشتری VIP فعال ندارید.</td></tr>@endforelse
   </tbody></table></div>
   {{ $memberships->links() }}
  </div></div>
 </div></div>
</div>
@endsection

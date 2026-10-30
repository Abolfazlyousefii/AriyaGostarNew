@extends('back.layouts.master')

@section('content')
<div class="app-content content" dir="rtl">
  <div class="content-wrapper">
    <div class="content-header row"><div class="col-12"><h3>مسیر فعالیت و خطاهای مشتری</h3></div></div>
    <div class="content-body">
      <div class="card"><div class="card-body">
        <p><strong>مشتری:</strong> {{ $user->fullname }} (شناسه {{ $user->id }})</p>
        <p class="text-muted">رویدادها از زمان فعال‌سازی این قابلیت ثبت می‌شوند. برای بازدیدهای قدیمی‌تر، <a href="{{ route('admin.users.views', ['user' => $user->id]) }}">تاریخچه بازدیدهای قبلی</a> را بررسی کنید.</p>
        <form method="get" action="{{ route('admin.customer-journey.index', ['user' => $user->id]) }}" class="form-inline mb-2">
          <label class="mr-1">از:</label><input name="from" type="date" class="form-control mr-2" value="{{ $filter['from'] }}" required>
          <label class="mr-1">تا:</label><input name="to" type="date" class="form-control mr-2" value="{{ $filter['to'] }}" required>
          <button class="btn btn-primary mr-2" type="submit">فیلتر</button>
          <a class="btn btn-success" href="{{ route('admin.customer-journey.download', ['user' => $user->id, 'from' => $filter['from'], 'to' => $filter['to']]) }}">دانلود گزارش JSONL</a>
        </form>
        <p class="text-muted">فایل خروجی برای بررسی باگ است؛ اطلاعات خصوصی فرم، توکن و پارامترهای URL در آن ذخیره نمی‌شود. فایل را فقط از مسیر امن به اشتراک بگذارید.</p>
        <div class="table-responsive"><table class="table table-striped table-bordered">
          <thead><tr><th>زمان</th><th>نوع</th><th>رویداد</th><th>مسیر</th><th>وضعیت HTTP</th><th>مدت (ms)</th><th>خطا</th><th>شناسه درخواست</th></tr></thead>
          <tbody>
          @forelse($events as $event)
            <tr>
              <td dir="ltr">{{ $event->created_at }}</td>
              <td>{{ $event->kind }}</td>
              <td>{{ $event->event }}</td>
              <td dir="ltr">{{ $event->path }}</td>
              <td>{{ $event->http_status ?? '—' }}</td>
              <td>{{ $event->duration_ms ?? '—' }}</td>
              <td>{{ $event->error_class ?? '—' }}</td>
              <td dir="ltr">{{ $event->request_id ?? '—' }}</td>
            </tr>
          @empty <tr><td colspan="8">رویدادی در این بازه ثبت نشده است.</td></tr>
          @endforelse
          </tbody>
        </table></div>
        {{ $events->links() }}
      </div></div>
    </div>
  </div>
</div>
@endsection

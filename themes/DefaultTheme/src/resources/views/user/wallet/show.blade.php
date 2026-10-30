@php
	$isDeposit = $history->type === 'credit';
	$isSuccess = $history->status === 'success';
	$sourceLabel = $history->source === 'admin' ? 'سیستم / مدیریت' : 'کاربر';
@endphp


<div class="table-responsive">
	<table class="table">
		<tbody>
		<tr>
			<th scope="row" style="min-width: 180px;">شماره تراکنش</th>
			<td>{{ $history->id }}</td>
		</tr>
		<tr>
			<th scope="row">مبلغ</th>
			<td>
				<strong class="{{ $isDeposit ? 'text-success' : 'text-danger' }}">
					{{ $isDeposit ? '+' : '-' }}{{ number_format((int) $history->amount) }} تومان
				</strong>
			</td>
		</tr>
		<tr>
			<th scope="row">نوع</th>
			<td>
				<span class="badge badge-pill {{ $isDeposit ? 'badge-success' : 'badge-danger' }}">
					{{ $isDeposit ? 'افزایش اعتبار' : 'برداشت' }}
				</span>
			</td>
		</tr>
		<tr>
			<th scope="row">وضعیت</th>
			<td>
				<span class="badge badge-pill {{ $isSuccess ? 'badge-success' : 'badge-danger' }}">
					{{ $isSuccess ? 'موفق' : 'ناموفق' }}
				</span>
			</td>
		</tr>
		<tr>
			<th scope="row">منبع</th>
			<td>{{ $sourceLabel }}</td>
		</tr>
		<tr>
			<th scope="row">تاریخ</th>
			<td class="ltr">{{ jdate($history->created_at) }}</td>
		</tr>
		<tr>
			<th scope="row">شرح</th>
			<td>{{ $history->note ?: '—' }}</td>
		</tr>
		@if($history->order_id)
			<tr>
				<th scope="row">سفارش</th>
				<td>#{{ $history->order_id }}</td>
			</tr>
		@endif
		@if($history->shipping_cost)
			<tr>
				<th scope="row">نوع پرداخت</th>
				<td>هزینه ارسال سفارش</td>
			</tr>
		@endif
		</tbody>
	</table>
</div>

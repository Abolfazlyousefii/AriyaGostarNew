@extends('front::user.layouts.master')

@section('user-content')
	@php
		$status = $wallet->balance_status;
		$statusLabel = $wallet->balance_status_label;
		$walletBalance = (int) $wallet->wallet_balance;
		$amount = abs($walletBalance);
		$statusClass = match ($status) {
			'debtor' => 'danger',
			'creditor' => 'success',
			default => 'secondary',
		};
		$statusIcon = match ($status) {
			'debtor' => 'mdi-alert-circle-outline',
			'creditor' => 'mdi-check-circle-outline',
			default => 'mdi-check-all',
		};
	@endphp

	<div class="col-xl-9 col-lg-8 col-md-8 col-sm-12">
		<style>
			.customer-balance-card{border:0;border-radius:16px;overflow:hidden;box-shadow:0 8px 28px rgba(0,0,0,.06);background:#fff}
			.customer-balance-card .balance-head{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:22px 24px;flex-wrap:wrap}
			.customer-balance-card .balance-title{font-size:13px;color:#777;margin-bottom:7px}
			.customer-balance-card .balance-value{font-size:24px;font-weight:800;line-height:1.4}
			.customer-balance-card .balance-badge{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:999px;font-weight:700;font-size:13px}
			.customer-balance-card .balance-badge.debtor{background:#fff1f1;color:#c62828}
			.customer-balance-card .balance-badge.creditor{background:#edf9f0;color:#218739}
			.customer-balance-card .balance-badge.settled{background:#f2f3f5;color:#60656f}
			.customer-balance-card .balance-hint{border-top:1px solid #f0f0f0;padding:13px 24px;color:#777;font-size:12px;line-height:1.9}
			.wallet-ledger-type{display:inline-flex;align-items:center;gap:5px;padding:5px 9px;border-radius:999px;font-size:12px;font-weight:700;white-space:nowrap}
			.wallet-ledger-type.debit{background:#fff1f1;color:#c62828}
			.wallet-ledger-type.credit{background:#edf9f0;color:#218739}
			.wallet-amount{font-weight:800;white-space:nowrap}
			.wallet-note{min-width:190px;line-height:1.8}
			@media(max-width:767px){.customer-balance-card .balance-value{font-size:20px}.customer-balance-card .balance-head{padding:18px}}
		</style>

		@if(session('message') == 'ok')
			<div class="alert alert-success mt-3" role="alert">
				<strong>{{ trans('front::messages.wallet.inventory-increase-successful') }}</strong>
			</div>
		@elseif(session('transaction-error'))
			<div class="alert alert-danger mt-3" role="alert">
				<strong>{{ session('transaction-error') }}</strong>
			</div>
		@endif

		<div class="customer-balance-card mb-4">
			<div class="balance-head">
				<div>
					<div class="balance-title">وضعیت حساب شما</div>
					<div class="balance-value">
						{{ number_format($walletBalance) }} تومان
					</div>
				</div>

				<div class="balance-badge {{ $status }}">
					<i class="mdi {{ $statusIcon }}"></i>
					{{ $statusLabel }}
				</div>
			</div>

			<div class="balance-hint">
				@if($status === 'debtor')
					شما در حال حاضر <strong>{{ number_format($amount) }} تومان بدهکار</strong> هستید.
				@elseif($status === 'creditor')
					شما در حال حاضر <strong>{{ number_format($amount) }} تومان بستانکار</strong> هستید و این مبلغ اعتبار قابل استفاده شماست.
				@else
					حساب شما در حال حاضر <strong>تسویه</strong> است و مانده‌ای ندارید.
				@endif
			</div>
		</div>

		<div class="section-title text-sm-title title-wide mb-1 no-after-title-wide dt-sl mb-2 px-res-1">
			<h2>گردش حساب</h2>
			<a href="{{ route('front.wallet.create') }}" class="m-0 d-block">{{ $status === 'debtor' ? 'پرداخت بدهی' : 'افزایش اعتبار / پرداخت' }}</a>
		</div>

		@if($histories->count())
			<div class="dt-sl">
				<div class="table-responsive">
					<table class="table table-order">
						<thead>
						<tr>
							<th>#</th>
							<th>مبلغ</th>
							<th>نوع گردش</th>
							<th>شرح</th>
							<th>تاریخ</th>
							<th>جزئیات</th>
						</tr>
						</thead>
						<tbody>
						@foreach ($histories as $history)
							@php($isCredit = $history->type === 'credit')
							<tr>
								<td>{{ $histories->firstItem() + $loop->index }}</td>
								<td class="wallet-amount {{ $isCredit ? 'text-success' : 'text-danger' }}">
									{{ number_format((int) $history->amount) }} تومان
								</td>
								<td>
                                    <span class="wallet-ledger-type {{ $isCredit ? 'credit' : 'debit' }}">
                                        <i class="mdi {{ $isCredit ? 'mdi-arrow-down-left' : 'mdi-arrow-up-right' }}"></i>
                                        {{ $isCredit ? 'بستانکاری' : 'بدهکاری' }}
                                    </span>
								</td>
								<td class="wallet-note">{{ $history->note ?: '—' }}</td>
								<td class="ltr">{{ jdate($history->created_at) }}</td>
								<td class="details-link">
									<a class="show-history"
									   data-action="{{ route('front.wallet.show', ['wallet' => $history]) }}"
									   href="#" onclick="return false;">
										<i class="mdi mdi-chevron-left"></i>
									</a>
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
		@else
			<div class="page dt-sl dt-sn pt-3 pb-3">
				<p class="mb-0">هنوز گردش حسابی برای شما ثبت نشده است.</p>
			</div>
		@endif

		<div class="mt-3">
			{{ $histories->links('front::components.paginate') }}
		</div>
	</div>
@endsection

@push('scripts')
	<div class="modal fade" id="history-show-modal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">جزئیات گردش حساب</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div id="history-detail" class="modal-body"></div>
			</div>
		</div>
	</div>

	<script src="{{ theme_asset('js/pages/wallet/index.js') }}"></script>
@endpush

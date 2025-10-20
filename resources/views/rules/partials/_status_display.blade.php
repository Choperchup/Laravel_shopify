{{-- resources/views/rules/partials/_status_display.blade.php --}}
@php
    $now = \Carbon\Carbon::now();
    $status = $rule->status;
@endphp

@if ($status == 'ACTIVATING' || $status == 'DEACTIVATING')
    <span class="badge status-processing">
        <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
        {{ $status == 'ACTIVATING' ? 'Đang kích hoạt' : 'Đang hủy' }}:
        <strong>{{ $rule->processed_products }} / {{ $rule->total_products }}</strong>
    </span>
@elseif ($status == 'PENDING_ACTIVATION' || $status == 'PENDING_DEACTIVATION')
    <span class="badge status-pending">
        <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
        {{ $status == 'PENDING_ACTIVATION' ? 'Chờ kích hoạt' : 'Chờ hủy' }}
    </span>
@elseif ($status == 'ACTIVE')
    <span class="badge status-active">
        Hoạt động từ {{ \Carbon\Carbon::parse($rule->activated_at)->format('H:i d/m/Y') }}
    </span>
@elseif ($status == 'SCHEDULED')
    <span class="badge status-future">
        Bắt đầu lúc {{ \Carbon\Carbon::parse($rule->start_at)->format('H:i d/m/Y') }}
    </span>
@elseif ($rule->end_at && $rule->end_at < $now)
    <span class="badge status-past">
        Dừng lúc {{ \Carbon\Carbon::parse($rule->end_at)->format('H:i d/m/Y') }}
    </span>
@elseif ($status == 'FAILED')
    <span class="badge status-failed">Thất bại</span>
@else
    <span class="badge status-inactive">Không hoạt động</span>
@endif
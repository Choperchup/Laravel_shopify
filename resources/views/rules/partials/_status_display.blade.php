{{-- resources/views/rules/partials/_status_display.blade.php --}}
@php
    $now = \Carbon\Carbon::now();
    $status = $rule->status;

    // ✅ FIX: PARSE end_at AN TOÀN + SO SÁNH CARBON OBJECT
    $endAt = null;
    $isExpired = false;

    if ($rule->end_at) {
        try {
            $endAt = \Carbon\Carbon::parse($rule->end_at);
            $isExpired = $endAt->lt($now); // ✅ SO SÁNH CARBON OBJECT
        } catch (\Exception $e) {
            Log::error("Cannot parse end_at for rule {$rule->id}: " . $rule->end_at);
            $isExpired = false; // Mặc định KHÔNG expire nếu parse lỗi
        }
    }
@endphp

{{-- ✅ DEBUG: XEM KẾT QUẢ SO SÁNH (XÓA SAU) --}}
{{-- Rule {{ $rule->id }}: now={{ $now->format('H:i') }} | end_at={{ $endAt?->format('H:i') }} | expired={{ $isExpired ?
'YES' : 'NO' }} --}}

{{-- 1. PROCESSING --}}
@if ($status == 'ACTIVATING' || $status == 'DEACTIVATING')
    <span class="badge status-processing">
        <div class="spinner-border spinner-border-sm"></div>
        {{ $status == 'ACTIVATING' ? 'Đang kích hoạt' : 'Đang hủy' }}:
        <strong>{{ $rule->processed_products }} / {{ $rule->total_products }}</strong>
    </span>

    {{-- 2. PENDING --}}
@elseif ($status == 'PENDING_ACTIVATION' || $status == 'PENDING_DEACTIVATION')
    <span class="badge status-pending">
        <div class="spinner-border spinner-border-sm"></div>
        {{ $status == 'PENDING_ACTIVATION' ? 'Chờ kích hoạt' : 'Chờ hủy' }}
    </span>

    {{-- 3. ACTIVE (CHỈ KHI KHÔNG EXPIRE) --}}
@elseif ($status == 'ACTIVE' && !$isExpired)
    <span class="badge status-active">
        Hoạt động từ {{ \Carbon\Carbon::parse($rule->activated_at)->format('H:i d/m/Y') }}
    </span>

    {{-- 4. EXPIRED (ƯU TIÊN CAO NHẤT) --}}
@elseif ($isExpired)
    <span class="badge status-past">
        Dừng lúc {{ $endAt->format('H:i d/m/Y') }}
    </span>

    {{-- 5. SCHEDULED --}}
@elseif ($status == 'SCHEDULED')
    <span class="badge status-future">
        Bắt đầu lúc {{ \Carbon\Carbon::parse($rule->start_at)->format('H:i d/m/Y') }}
    </span>

    {{-- 6. FAILED --}}
@elseif ($status == 'FAILED')
    <span class="badge status-failed">Thất bại</span>

    {{-- 7. INACTIVE --}}
@else
    <span class="badge status-inactive">Không hoạt động</span>
@endif
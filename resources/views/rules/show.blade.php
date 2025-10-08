<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Quy tắc: {{ $rule->name }}</title>
    <!-- Thêm Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .detail-card {
            padding: 20px;
            margin-bottom: 20px;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-future {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-past {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-archived {
            background-color: #e9ecef;
            color: #495057;
        }

        .condition-item {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <!-- Tiêu đề và nút quay lại -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Chi tiết Quy tắc: {{ $rule->name }}</h2>
            <a href="{{ route('rules.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>

        <!-- Thẻ thông tin chi tiết -->
        <div class="card detail-card">
            <div class="card-body">
                <!-- Thông tin tổng quan -->
                <h4 class="card-title">Thông tin Quy tắc</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tên Quy tắc:</strong> {{ $rule->name }}</p>
                        <p><strong>Giá trị Giảm giá:</strong> {{ $rule->discount_value }}
                            {{ $rule->discount_type === 'percentage' ? '%' : app(ProductGraphQLService::class)->getCurrency($rule->getShop()) }}
                        </p>
                        <p><strong>Loại Giảm giá:</strong>
                            {{ $rule->discount_type === 'percentage' ? 'Phần trăm' : 'Số tiền cố định' }}</p>
                        <p><strong>Cơ sở Giảm giá:</strong>
                            {{ $rule->discount_base === 'current_price' ? 'Giá hiện tại' : 'Giá so sánh' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Thời gian Bắt đầu:</strong>
                            {{ $rule->start_at ? $rule->start_at->format('h:i a, d M Y') : 'Không đặt' }}</p>
                        <p><strong>Thời gian Kết thúc:</strong>
                            {{ $rule->end_at ? $rule->end_at->format('h:i a, d M Y') : 'Không đặt' }}</p>
                        <p><strong>Thẻ Thêm:</strong> {{ implode(', ', $rule->tags_to_add ?? []) ?: 'Không có' }}</p>
                    </div>
                </div>

                <!-- Điều kiện áp dụng -->
                <h4 class="card-title mt-4">Điều kiện Áp dụng</h4>
                <div class="row">
                    <div class="col-md-12">
                        @foreach ($rule->conditions_display as $condition)
                            <p class="condition-item">{{ $condition }}</p>
                        @endforeach
                    </div>
                </div>

                <!-- Trạng thái -->
                <h4 class="card-title mt-4">Trạng thái</h4>
                <p>
                    <span class="badge @if(str_contains($rule->status_display, 'Hoạt động')) status-active
                    @elseif(str_contains($rule->status_display, 'Bắt đầu')) status-future
                    @elseif(str_contains($rule->status_display, 'Dừng')) status-past
                    @elseif(str_contains($rule->status_display, 'Không hoạt động')) status-inactive
                    @elseif(str_contains($rule->status_display, 'Đã lưu trữ')) status-archived
                    @endif">
                        {{ $rule->status_display }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Hành động -->
        <div class="card detail-card">
            <div class="card-body text-end">
                <a href="{{ route('rules.edit', $rule) }}" class="btn btn-info me-2">Chỉnh sửa</a>
                @if ($rule->archived_at === null)
                    <form action="{{ route('rules.toggle', $rule) }}" method="POST" style="display:inline-block;"
                        class="me-2">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn {{ $rule->active ? 'btn-danger' : 'btn-success' }}">
                            {{ $rule->active ? 'Tắt' : 'Bật' }}
                        </button>
                    </form>
                    <form action="{{ route('rules.archive', $rule) }}" method="POST" style="display:inline-block;"
                        class="me-2">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-warning">Lưu trữ</button>
                    </form>
                @else
                    <form action="{{ route('rules.restore', $rule) }}" method="POST" style="display:inline-block;">
                        @csrf
                        <button type="submit" class="btn btn-secondary me-2">Khôi phục</button>
                    </form>

                    <form action="{{ route('rules.destroy', $rule) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Thêm Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Quy tắc</title>
    <!-- Thêm Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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

        .table-responsive {
            max-height: 70vh;
            overflow-y: auto;
        }

        .filter-section {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <!-- Tiêu đề và nút tạo quy tắc -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Dashboard / Danh sách Quy tắc</h2>
            <a href="{{ route('rules.create') }}" class="btn btn-primary">Tạo Quy tắc</a>
        </div>

        <!-- Thẻ Main/Archived -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab === 'main' ? 'active' : '' }}"
                    href="{{ route('rules.index', ['tab' => 'main']) }}">Main</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab === 'archived' ? 'active' : '' }}"
                    href="{{ route('rules.index', ['tab' => 'archived']) }}">Archived</a>
            </li>
        </ul>

        <!-- Bộ lọc -->
        <div class="filter-section card p-3">
            <form method="GET" action="{{ route('rules.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Tìm theo tên quy tắc">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="future" {{ request('status') === 'future' ? 'selected' : '' }}>Sắp hoạt động
                        </option>
                        <option value="past" {{ request('status') === 'past' ? 'selected' : '' }}>Đã dừng</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Không hoạt động
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="apply_to" class="form-select">
                        <option value="">Tất cả áp dụng cho</option>
                        <option value="products" {{ request('apply_to') === 'products' ? 'selected' : '' }}>Sản phẩm
                        </option>
                        <option value="collections" {{ request('apply_to') === 'collections' ? 'selected' : '' }}>Bộ sưu
                            tập</option>
                        <option value="tags" {{ request('apply_to') === 'tags' ? 'selected' : '' }}>Thẻ</option>
                        <option value="vendors" {{ request('apply_to') === 'vendors' ? 'selected' : '' }}>Nhà cung cấp
                        </option>
                        <option value="whole_store" {{ request('apply_to') === 'whole_store' ? 'selected' : '' }}>Toàn cửa
                            hàng</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="discount_type" class="form-select">
                        <option value="">Tất cả loại giảm giá</option>
                        <option value="percentage" {{ request('discount_type') === 'percentage' ? 'selected' : '' }}>Phần
                            trăm</option>
                        <option value="fixed_amount" {{ request('discount_type') === 'fixed_amount' ? 'selected' : '' }}>
                            Số tiền cố định</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="min_discount" value="{{ request('min_discount') }}" class="form-control"
                        placeholder="Giá trị giảm tối thiểu">
                </div>
                <div class="col-md-2">
                    <input type="number" name="max_discount" value="{{ request('max_discount') }}" class="form-control"
                        placeholder="Giá trị giảm tối đa">
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_from" value="{{ request('start_from') }}" class="form-control"
                        placeholder="Bắt đầu từ">
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_to" value="{{ request('start_to') }}" class="form-control"
                        placeholder="Bắt đầu đến">
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Tên</option>
                        <option value="start_at" {{ request('sort') === 'start_at' ? 'selected' : '' }}>Thời gian bắt đầu
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="dir" class="form-select">
                        <option value="asc" {{ request('dir') === 'asc' ? 'selected' : '' }}>Tăng dần</option>
                        <option value="desc" {{ request('dir') === 'desc' ? 'selected' : '' }}>Giảm dần</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
            </form>
        </div>

        <!-- Bảng danh sách quy tắc -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Tên Quy tắc</th>
                        <th>Điều kiện</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rules as $rule)
                        <tr>
                            <td>{{ $rule->name }}</td>
                            <td>
                                @foreach ($rule->conditions_display as $line)
                                    {{ $line }}<br>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge @if(str_contains($rule->status_display, 'Hoạt động')) status-active
                                @elseif(str_contains($rule->status_display, 'Bắt đầu')) status-future
                                    @elseif(str_contains($rule->status_display, 'Dừng')) status-past
                                        @elseif(str_contains($rule->status_display, 'Không hoạt động')) status-inactive
                                            @elseif(str_contains($rule->status_display, 'Đã lưu trữ')) status-archived
                                                @endif">
                                    {{ $rule->status_display }}
                                </span>
                            </td>
                            <td>
                                @if ($tab === 'main')
                                    <a href="{{ route('rules.duplicate', $rule) }}" class="btn btn-sm btn-secondary me-1">Sao
                                        chép</a>
                                    <a href="{{ route('rules.edit', $rule) }}" class="btn btn-sm btn-info me-1">Chỉnh sửa</a>
                                    <form action="{{ route('rules.archive', $rule) }}" method="POST" style="display:inline;">
                                        @csrf @method('POST')
                                        <button type="submit" class="btn btn-sm btn-warning">Lưu trữ</button>
                                    </form>
                                    @if ($rule->active)
                                        <form action="{{ route('rules.toggle', $rule) }}" method="POST" style="display:inline;"
                                            class="ms-1">
                                            @csrf @method('POST')
                                            <button type="submit" class="btn btn-sm btn-danger">Tắt</button>
                                        </form>
                                    @else
                                        <form action="{{ route('rules.toggle', $rule) }}" method="POST" style="display:inline;"
                                            class="ms-1">
                                            @csrf @method('POST')
                                            <button type="submit" class="btn btn-sm btn-success">Bật</button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('rules.restore', $rule) }}" class="btn btn-sm btn-secondary me-1">Khôi
                                        phục</a>
                                    <form action="{{ route('rules.destroy', $rule) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <div class="d-flex justify-content-center mt-3">
            {{ $rules->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Thêm Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
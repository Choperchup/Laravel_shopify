<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ========== STATUS BADGES ========== */
        .status-active {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .status-future,
        .status-past {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .status-archived {
            background-color: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 4px;
        }

        /* ========== TABLE ========== */
        .table thead th {
            white-space: nowrap;
            vertical-align: middle;
        }

        .action-btns button,
        .action-btns a {
            margin-right: 5px;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ========== PAGINATION FIX ========== */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
        }

        .pagination .page-link {
            color: #0d6efd;
            border-radius: 6px;
            font-size: 0.9rem;
            padding: 6px 12px;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
            color: #0a58ca;
        }

        /* ✅ FIX icon quá to */
        .pagination svg,
        .pagination i {
            width: 1em !important;
            height: 1em !important;
            font-size: 1rem !important;
            vertical-align: middle;
        }

        /* Giới hạn icon nếu có svg render ra */
        svg {
            width: auto;
            height: auto;
            max-width: 16px;
            max-height: 16px;
        }

        .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md {
            display: none !important;
        }

        .status-processing,
        .status-pending {
            background-color: #0dcaf0;
            color: white;
        }

        .status-failed {
            background-color: #dc3545;
            color: white;
        }
    </style>

</head>

<body>
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Dashboard / <strong>My Rules</strong></h4>
            <a href="{{ route('rules.create', ['host' => request('host'), 'shop' => request('shop')]) }}"
                class="btn btn-dark">Create Rule</a>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab === 'main' ? 'active' : '' }}"
                    href="{{ route('rules.index', array_merge(['tab' => 'main'], ['host' => request('host'), 'shop' => request('shop')])) }}">Main</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab === 'archived' ? 'active' : '' }}"
                    href="{{ route('rules.index', array_merge(['tab' => 'archived'], ['host' => request('host'), 'shop' => request('shop')])) }}">Archived</a>
            </li>
        </ul>

        <!-- Toolbar (Search + Filter + Refresh) -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div></div>
            <div class="toolbar-right">
                <form method="GET"
                    action="{{ route('rules.index', ['host' => request('host'), 'shop' => request('shop')]) }}"
                    class="d-flex">

                    <input type="hidden" name="tab" value="{{ $tab }}"> <!-- giữ tab hiện tại -->
                    <!-- Search box -->
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control form-control-sm" placeholder="Search Rule..." style="width:200px;">
                    <!-- Apply button -->
                    <button class="btn btn-outline-secondary btn-sm ms-2" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <!-- Filter dropdown -->
                <form method="GET" action="{{ route('rules.index') }}">
                    <!-- Filter dropdown -->
                    <input type="hidden" name="tab" value="{{ $tab }}"> <!-- ✅ Giữ tab hiện tại -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                            id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-funnel"></i> Filter
                        </button>

                        <!-- Nội dung filter -->
                        <div class="dropdown-menu p-3" style="min-width: 280px;">

                            <!-- Status -->
                            <div class="mb-2">
                                <label class="form-label form-label-sm mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                </select>
                            </div>

                            <!-- Apply To -->
                            <div class="mb-2">
                                <label class="form-label form-label-sm mb-1">Apply To</label>
                                <select name="apply_to" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="products" {{ request('apply_to') == 'products' ? 'selected' : '' }}>
                                        Products</option>
                                    <option value="collections" {{ request('apply_to') == 'collections' ? 'selected' : '' }}>Collections</option>
                                    <option value="tags" {{ request('apply_to') == 'tags' ? 'selected' : '' }}>Tags
                                    </option>
                                    <option value="vendors" {{ request('apply_to') == 'vendors' ? 'selected' : '' }}>
                                        Vendors</option>
                                </select>
                            </div>

                            <!-- Discount Type -->
                            <div class="mb-2">
                                <label class="form-label form-label-sm mb-1">Discount Type</label>
                                <select name="discount_type" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="percentage" {{ request('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="fixed_amount" {{ request('discount_type') == 'fixed_amount' ? 'selected' : '' }}>Fixed</option>
                                </select>
                            </div>

                            <!-- Start / End date -->
                            <div class="mb-2">
                                <label class="form-label form-label-sm mb-1">Start / End</label>
                                <div class="d-flex gap-1">
                                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                                        class="form-control form-control-sm">
                                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                                        class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- Sort -->
                            <div class="mb-2">
                                <label class="form-label form-label-sm mb-1">Sort</label>
                                <select name="sort" class="form-select form-select-sm">
                                    <option value="">Default</option>
                                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name ↑
                                    </option>
                                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name
                                        ↓</option>
                                    <option value="start_asc" {{ request('sort') == 'start_asc' ? 'selected' : '' }}>Start
                                        ↑</option>
                                    <option value="start_desc" {{ request('sort') == 'start_desc' ? 'selected' : '' }}>
                                        Start ↓</option>
                                </select>
                            </div>

                            <!-- Submit -->
                            <div class="text-end">
                                <button class="btn btn-sm btn-primary" type="submit">
                                    <i class="bi bi-check2"></i> Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Refresh button -->
                <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>Rule Name</th>
                        <th>Conditions</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rules as $rule)
                        <tr id="rule-row-{{ $rule->id }}" data-status="{{ $rule->status }}">
                            <td><input type="checkbox"></td>
                            <td>{{ $rule->name }}</td>
                            <td>
                                @foreach ($rule->conditions_display as $line)
                                    {{ $line }}<br>
                                @endforeach
                            </td>
                            <td class="status-cell">
                                @include('rules.partials._status_display', ['rule' => $rule])
                            </td>
                            <td class="action-btns">
                                {{-- NÚT TURN ON/OFF MỚI --}}
                                <form
                                    action="{{ route('rules.toggle', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                    method="POST" style="display:inline;">
                                    @csrf
                                    {{-- Logic mới dựa trên cột `is_enabled` --}}
                                    @if ($rule->is_enabled)
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Turn off</button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success">Turn on</button>
                                    @endif
                                </form>
                                @if ($tab === 'main')
                                    <form
                                        action="{{ route('rules.duplicate', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary">⧉</button>
                                    </form>

                                    <a href="{{ route('rules.edit', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                        class="btn btn-sm btn-info">✎</a>
                                    <form
                                        action="{{ route('rules.archive', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">⤵</button>
                                    </form>

                                    {{-- ✅ Thêm nút XÓA trực tiếp ở Main --}}
                                    <form action="{{ route('rules.destroy', $rule) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bạn có chắc muốn xóa rule này?')">🗑</button>
                                    </form>
                                @else
                                    <form
                                        action="{{ route('rules.restore', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary">↺</button>
                                    </form>
                                    <form action="{{ route('rules.destroy', $rule) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Bạn có chắc muốn xóa?')">🗑</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $rules->appends(array_merge(request()->query(), ['host' => request('host'), 'shop' => request('shop')]))->links() }}
        </div>
    </div>

    <!-- Bootstrap Icons + JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pollStatuses = () => {
                // Chỉ tìm những dòng có trạng thái cần cập nhật
                document.querySelectorAll("tr[data-status*='PENDING'], tr[data-status*='ACTIVATING'], tr[data-status*='DEACTIVATING']").forEach(row => {
                    const ruleId = row.id.split('-')[2];

                    // === SỬA LỖI Ở ĐÂY: Xây dựng URL sạch ===
                    const host = "{{ request('host') }}";
                    const shop = "{{ request('shop') }}";
                    const fetchUrl = `{{ url('/') }}/rules/${ruleId}/status-display?host=${host}&shop=${shop}`;
                    // =======================================

                    // Gọi tới route đã tạo ở web.php để lấy HTML mới nhất
                    fetch(fetchUrl)
                        .then(response => {
                            if (!response.ok) {
                                // Nếu gặp lỗi 404 hoặc lỗi server khác, ghi lại để dễ debug
                                console.error(`Error fetching status for rule ${ruleId}: ${response.statusText}`);
                                return ''; // Trả về chuỗi rỗng để không làm hỏng giao diện
                            }
                            return response.text();
                        })
                        .then(html => {
                            if (html) {
                                const statusCell = row.querySelector('.status-cell');
                                if (statusCell) {
                                    statusCell.innerHTML = html;
                                    // Cập nhật lại data-status để script biết khi nào cần dừng polling
                                    if (!html.includes('Chờ') && !html.includes('Đang')) {
                                        row.removeAttribute('data-status');
                                    }
                                }
                            }
                        })
                        .catch(error => console.error('Error polling status:', error));
                });
            };

            // Bắt đầu polling, lặp lại mỗi 5 giây
            setInterval(pollStatuses, 5000);
        });
    </script>
</body>

</html>
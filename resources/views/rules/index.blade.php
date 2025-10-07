<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control form-control-sm" placeholder="Search Rule..." style="width:200px;">
                    <button class="btn btn-outline-secondary btn-sm ms-2" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
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
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rules as $rule)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>{{ $rule->name }}</td>
                            <td>
                                @foreach ($rule->conditions_display as $line)
                                    {{ $line }}<br>
                                @endforeach
                            </td>
                            <td>
                                <span class="
                                                                @if(str_contains($rule->status_display, 'Hoạt động')) status-active
                                                                @elseif(str_contains($rule->status_display, 'Bắt đầu')) status-future
                                                                @elseif(str_contains($rule->status_display, 'Dừng')) status-past
                                                                @elseif(str_contains($rule->status_display, 'Không hoạt động')) status-inactive
                                                                @elseif(str_contains($rule->status_display, 'Đã lưu trữ')) status-archived
                                                                @endif">
                                    {{ $rule->status_display }}
                                </span>
                            </td>
                            <td>
                                @if ($rule->active)
                                    <form
                                        action="{{ route('rules.toggle', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Turn off</button>
                                    </form>
                                @else
                                    <form
                                        action="{{ route('rules.toggle', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">Turn on</button>
                                    </form>
                                @endif
                            </td>
                            <td class="action-btns">
                                @if ($tab === 'main')
                                    <form
                                        action="{{ route('rules.duplicate', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary">⧉</button>
                                    </form>

                                    <a href="{{ route('rules.edit', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                        class="btn btn-sm btn-info">✎</a>
                                    <form action="{{ route('rules.archive', $rule) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">⤵</button>
                                    </form>
                                @else
                                    <a href="{{ route('rules.restore', ['rule' => $rule, 'host' => request('host'), 'shop' => request('shop')]) }}"
                                        class="btn btn-sm btn-secondary">↺</a>
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
</body>

</html>
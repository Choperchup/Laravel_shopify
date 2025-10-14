<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chỉnh sửa Quy tắc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --p-surface: #ffffff;
            --p-surface-subdued: #f9fafb;
            --p-border: #e1e3e5;
            --p-border-subdued: #e1e3e5;
            --p-shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.05);
            --p-space-4: 1rem;
            --p-space-5: 1.25rem;
            --p-text: #212b36;
            --p-text-subdued: #637381;
            --p-interactive: #5c6ac4;
        }

        body {
            background-color: #f6f6f7;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            color: var(--p-text);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        h2.page-title {
            font-weight: 600;
            font-size: 1.75rem;
            margin: 0;
        }

        .card {
            background: var(--p-surface);
            border: 1px solid var(--p-border);
            border-radius: 8px;
            box-shadow: var(--p-shadow-xs);
            margin-bottom: var(--p-space-5);
        }

        .card-header {
            padding: var(--p-space-4) var(--p-space-5);
            background-color: var(--p-surface);
            border-bottom: 1px solid var(--p-border);
            font-weight: 600;
            font-size: 1rem;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .card-body {
            padding: var(--p-space-5);
        }

        label.form-label,
        .form-check-label {
            font-weight: 600;
            color: var(--p-text);
            margin-bottom: 0.4rem;
        }

        .form-check-label {
            font-weight: 400;
        }

        .form-control,
        .form-select,
        .select2-selection {
            border-radius: 6px !important;
            border-color: #c4cdd5 !important;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus,
        .select2-selection--multiple:focus-within {
            border-color: var(--p-interactive) !important;
            box-shadow: 0 0 0 2px rgba(92, 106, 196, 0.2) !important;
        }

        .btn-primary {
            background-color: #008060;
            border-color: #008060;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
        }

        .btn-primary:hover {
            background-color: #006e52;
            border-color: #006e52;
        }

        .btn-secondary {
            background-color: var(--p-surface);
            color: var(--p-text);
            border: 1px solid #c4cdd5;
        }

        .btn-secondary:hover {
            background-color: #f1f2f3;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .summary-card ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .summary-card li {
            padding: 8px 0;
            border-bottom: 1px solid var(--p-border-subdued);
            font-size: 0.9rem;
            color: var(--p-text-subdued);
        }

        .summary-card li:last-child {
            border-bottom: none;
        }

        .summary-card li .summary-value {
            color: var(--p-text);
            font-weight: 500;
        }

        .target-section {
            display: none;
        }

        .target-section.active {
            display: block;
        }

        .datetime-picker {
            width: 100%;
        }
    </style>
</head>

<body>
    <form action="{{ route('rules.update', ['rule' => $rule->id, 'host' => request('host'), 'shop' => request('shop')]) }}"
        method="POST">
        @csrf
        @method('PUT')
        <div class="container-fluid mt-4">
            <div class="page-header">
                <div>
                    <a href="{{ route('rules.index', ['host' => request('host'), 'shop' => request('shop')]) }}"
                        class="btn btn-secondary mb-3 me-3">← Quay lại</a>
                    <h2 class="page-title d-inline-block align-middle">Chỉnh sửa Quy tắc: {{ $rule->name }}</h2>
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật Quy tắc</button>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name" class="form-label">Tên Quy tắc <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required
                                    value="{{ old('name', $rule->name) }}">
                                <div class="form-text">Chỉ dùng để tham khảo nội bộ. Khách hàng không nhìn thấy thông tin này.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-8">
                                    <label for="discount_value" class="form-label">Giá trị Giảm giá <span class="text-danger">*</span></label>
                                    <input type="number" name="discount_value" id="discount_value" class="form-control"
                                        step="0.01" min="0" required value="{{ old('discount_value', $rule->discount_value) }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="discount_type" class="form-label invisible">Loại</label>
                                    <select name="discount_type" id="discount_type" class="form-select" required>
                                        <option value="percentage" {{ old('discount_type', $rule->discount_type) === 'percentage' ? 'selected' : '' }}>%</option>
                                        <option value="fixed_amount" {{ old('discount_type', $rule->discount_type) === 'fixed_amount' ? 'selected' : '' }}>Số tiền cố định</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label d-block">Cơ sở Giảm giá <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="discount_base" value="current_price" id="current_price"
                                        class="form-check-input" {{ old('discount_base', $rule->discount_base) === 'current_price' ? 'checked' : '' }}>
                                    <label for="current_price" class="form-check-label">Giá hiện tại</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="discount_base" value="compare_at_price" id="compare_at_price"
                                        class="form-check-input" {{ old('discount_base', $rule->discount_base) === 'compare_at_price' ? 'checked' : '' }}>
                                    <label for="compare_at_price" class="form-check-label">Giá so sánh</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            @php
                                $applyTargets = is_array($rule->apply_to_targets)
                                    ? $rule->apply_to_targets
                                    : (json_decode($rule->apply_to_targets, true) ?? []);
                                $excludeProducts = is_array($rule->exclude_products)
                                    ? $rule->exclude_products
                                    : (json_decode($rule->exclude_products, true) ?? []);
                            @endphp
                            <div class="form-group">
                                <label for="apply_to_type" class="form-label">Áp dụng cho <span class="text-danger">*</span></label>
                                <select name="apply_to_type" id="apply_to_type" class="form-select" required>
                                    <option value="products" {{ old('apply_to_type', $rule->apply_to_type) === 'products' ? 'selected' : '' }}>Sản phẩm và biến thể</option>
                                    <option value="collections" {{ old('apply_to_type', $rule->apply_to_type) === 'collections' ? 'selected' : '' }}>Bộ sưu tập</option>
                                    <option value="tags" {{ old('apply_to_type', $rule->apply_to_type) === 'tags' ? 'selected' : '' }}>Thẻ</option>
                                    <option value="vendors" {{ old('apply_to_type', $rule->apply_to_type) === 'vendors' ? 'selected' : '' }}>Nhà cung cấp</option>
                                    <option value="whole_store" {{ old('apply_to_type', $rule->apply_to_type) === 'whole_store' ? 'selected' : '' }}>Toàn cửa hàng</option>
                                </select>
                            </div>
                            <div class="target-section" id="products-section">
                                <label class="form-label">Chọn Sản phẩm</label>
                                <select name="apply_to_targets[]" id="products-select" class="form-control select2" multiple
                                    style="width: 100%;">
                                    @foreach ($products as $product)
                                        <option value="{{ $product['id'] }}" {{ in_array($product['id'], $applyTargets) ? 'selected' : '' }}>
                                            {{ $product['title'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="target-section" id="collections-section">
                                <label class="form-label">Chọn Bộ sưu tập</label>
                                <select name="apply_to_targets[]" id="collections-select" class="form-control select2" multiple
                                    style="width: 100%;">
                                    @foreach ($collections as $collection)
                                        <option value="{{ $collection['node']['id'] }}" {{ in_array($collection['node']['id'], $applyTargets) ? 'selected' : '' }}>
                                            {{ $collection['node']['title'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="target-section" id="tags-section">
                                <label class="form-label">Chọn Thẻ</label>
                                <select name="apply_to_targets[]" id="tags-select" class="form-control select2" multiple
                                    style="width: 100%;">
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag['node'] }}" {{ in_array($tag['node'], $applyTargets) ? 'selected' : '' }}>
                                            {{ $tag['node'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="target-section" id="vendors-section">
                                <label class="form-label">Chọn Nhà cung cấp</label>
                                <select name="apply_to_targets[]" id="vendors-select" class="form-control select2" multiple
                                    style="width: 100%;">
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor['node'] }}" {{ in_array($vendor['node'], $applyTargets) ? 'selected' : '' }}>
                                            {{ $vendor['node'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sản phẩm loại trừ</label>
                                <select name="exclude_products[]" id="exclude-products" class="form-control select2" multiple
                                    style="width: 100%;">
                                    @foreach ($products as $product)
                                        <option value="{{ $product['id'] }}" {{ in_array($product['id'], $excludeProducts) ? 'selected' : '' }}>
                                            {{ $product['title'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card summary-card">
                        <div class="card-header">TÓM TẮT</div>
                        <div class="card-body">
                            <ul>
                                <li>Tên Quy tắc: <span id="summary-rule-name" class="summary-value">{{ old('name', $rule->name) ?: 'Chưa đặt' }}</span></li>
                                <li>
                                    <span id="summary-discount" class="summary-value">{{ old('discount_value', $rule->discount_value) }}{{ $rule->discount_type === 'percentage' ? '%' : ' (cố định)' }}</span> Giảm giá cho
                                    <span id="summary-target-count" class="summary-value">0</span>
                                    <span id="summary-target-type">Sản phẩm</span>
                                </li>
                                <li>Giảm giá dựa trên <span id="summary-discount-base" class="summary-value">{{ old('discount_base', $rule->discount_base) === 'current_price' ? 'Giá hiện tại' : 'Giá so sánh' }}</span></li>
                                <li>Bắt đầu từ <span id="summary-start-date" class="summary-value">{{ old('start_at', optional($rule->start_at)->format('F j, Y (h:i A)')) ?: 'ngay bây giờ' }}</span></li>
                                <li>Thẻ được thêm: <span id="summary-tags" class="summary-value">{{ old('tags_to_add', is_array($rule->tags_to_add) ? implode(', ', $rule->tags_to_add) : $rule->tags_to_add) ?: 'Không có' }}</span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Thẻ Tùy chỉnh</div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label for="tags_to_add" class="form-label">Thêm thẻ</label>
                                <input type="text" name="tags_to_add" id="tags_to_add" class="form-control"
                                    value="{{ old('tags_to_add', is_array($rule->tags_to_add) ? implode(', ', $rule->tags_to_add) : $rule->tags_to_add) }}"
                                    placeholder="ví dụ: flash-sale, 24hr-deal">
                                <div class="form-text">Thẻ được thêm khi quy tắc được kích hoạt và bị xóa khi không hoạt động. Các thẻ cách nhau bằng dấu phẩy.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Ngày Bắt đầu</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-7">
                                    <label for="start_date" class="form-label">Ngày Bắt đầu</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                        value="{{ old('start_date', optional($rule->start_at)->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="start_time" class="form-label">Thời gian Bắt đầu</label>
                                    <input type="time" name="start_time" id="start_time" class="form-control"
                                        value="{{ old('start_time', optional($rule->start_at)->format('H:i')) }}">
                                </div>
                            </div>
                            <input type="datetime-local" name="start_at" id="start_at" class="d-none"
                                value="{{ old('start_at', optional($rule->start_at)->format('Y-m-d\TH:i')) }}">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="set-end-date-checkbox"
                                    {{ old('end_date') || $rule->end_at ? 'checked' : '' }}>
                                <label class="form-check-label" for="set-end-date-checkbox">Đặt Ngày Kết thúc</label>
                            </div>
                            <div id="end-date-wrapper" class="row" style="display: {{ old('end_date') || $rule->end_at ? 'flex' : 'none' }};">
                                <div class="form-group col-md-7">
                                    <label for="end_date" class="form-label">Ngày Kết thúc</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control"
                                        value="{{ old('end_date', optional($rule->end_at)->format('Y-m-d')) }}">
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="end_time" class="form-label">Thời gian Kết thúc</label>
                                    <input type="time" name="end_time" id="end_time" class="form-control"
                                        value="{{ old('end_time', optional($rule->end_at)->format('H:i')) }}">
                                </div>
                            </div>
                            <input type="datetime-local" name="end_at" id="end_at" class="d-none"
                                value="{{ old('end_at', optional($rule->end_at)->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function () {
            const urlParams = new URLSearchParams(window.location.search);
            const host = urlParams.get('host');
            const shop = urlParams.get('shop');

            $('.select2').select2({
                ajax: {
                    url: function () {
                        let baseUrl;
                        switch ($(this).attr('id')) {
                            case 'products-select':
                            case 'exclude-products':
                                baseUrl = '{{ route('api.products.search') }}';
                                break;
                            case 'collections-select':
                                baseUrl = '{{ route('api.collections.search') }}';
                                break;
                            case 'tags-select':
                                baseUrl = '{{ route('api.tags.search') }}';
                                break;
                            case 'vendors-select':
                                baseUrl = '{{ route('api.vendors.search') }}';
                                break;
                        }
                        if (host && shop) {
                            baseUrl += (baseUrl.includes('?') ? '&' : '?') + 'host=' + encodeURIComponent(host) + '&shop=' + encodeURIComponent(shop);
                        }
                        return baseUrl;
                    },
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term, page: params.page }),
                    processResults: data => ({
                        results: data.map(item => ({ id: item.id, text: item.title || item.node }))
                    }),
                    cache: true
                },
                placeholder: 'Tìm và chọn...',
                allowClear: true,
                minimumInputLength: 2
            });

            $('#apply_to_type').on('change', function () {
                $('.target-section').removeClass('active');
                if ($(this).val() !== 'whole_store') {
                    $('#' + $(this).val() + '-section').addClass('active');
                }
                updateSummary();
            }).trigger('change');

            // Combine date/time for start and end dates
            function combineDateTime(dateInput, timeInput, combinedInput) {
                const dateVal = $(dateInput).val();
                const timeVal = $(timeInput).val();
                if (dateVal && timeVal) {
                    $(combinedInput).val(`${dateVal}T${timeVal}`);
                } else if (dateVal) {
                    $(combinedInput).val(`${dateVal}T00:00`);
                } else {
                    $(combinedInput).val('');
                }
                $(combinedInput).trigger('change');
            }

            $('#start_date, #start_time').on('change', function () {
                combineDateTime('#start_date', '#start_time', '#start_at');
            });
            $('#end_date, #end_time').on('change', function () {
                combineDateTime('#end_date', '#end_time', '#end_at');
            });

            $('#set-end-date-checkbox').on('change', function () {
                $('#end-date-wrapper').toggle(this.checked);
                if (!this.checked) {
                    $('#end_date, #end_time, #end_at').val('').trigger('change');
                }
                updateSummary();
            });

            $('#start_at, #end_at').on('change', function () {
                const start = $('#start_at').val();
                const end = $('#end_at').val();
                if (start && end && new Date(start) >= new Date(end)) {
                    alert('Ngày bắt đầu phải trước ngày kết thúc!');
                    $('#end_date, #end_time, #end_at').val('');
                }
                updateSummary();
            });

            function updateSummary() {
                const ruleName = $('#name').val();
                $('#summary-rule-name').text(ruleName || 'Chưa đặt');

                const discountValue = $('#discount_value').val() || 0;
                const discountType = $('#discount_type').val() === 'percentage' ? '%' : ' (cố định)';
                $('#summary-discount').text(`${discountValue}${discountType}`);

                const applyToType = $('#apply_to_type').val();
                const targetSelectId = '#' + applyToType + '-select';
                let targetCount = 0;
                if (applyToType !== 'whole_store') {
                    targetCount = $(targetSelectId).select2('data').length;
                } else {
                    targetCount = 1;
                }

                let targetTypeName = applyToType.charAt(0).toUpperCase() + applyToType.slice(1);
                if (targetCount !== 1) {
                    targetTypeName = targetTypeName.endsWith('s') ? targetTypeName : targetTypeName + 's';
                }
                if (applyToType === 'whole_store') targetTypeName = "Cửa hàng";

                $('#summary-target-count').text(targetCount);
                $('#summary-target-type').text(targetTypeName);

                const discountBase = $('input[name="discount_base"]:checked').parent().find('label').text();
                $('#summary-discount-base').text(discountBase);

                const startDateVal = $('#start_at').val();
                if (startDateVal) {
                    const date = new Date(startDateVal);
                    const formattedDate = date.toLocaleDateString('vi-VN', { month: 'long', day: 'numeric', year: 'numeric' }) + ' (' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }) + ')';
                    $('#summary-start-date').text(formattedDate);
                } else {
                    $('#summary-start-date').text('ngay bây giờ');
                }

                const tags = $('#tags_to_add').val();
                $('#summary-tags').text(tags || 'Không có');
            }

            $('form').on('input change', 'input, select', updateSummary);
            $('.select2').on('change', updateSummary);
            updateSummary();

            $('#ruleForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.success) {
                            alert('Cập nhật thành công!');
                            loadRulesTable();
                        }
                    },
                    error: function (err) {
                        alert('Có lỗi xảy ra!');
                    }
                });
            });
        });
    </script>
</body>

</html>
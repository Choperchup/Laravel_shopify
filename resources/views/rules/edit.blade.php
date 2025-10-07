<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Quy tắc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .form-group {
            margin-bottom: 1.5rem;
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
    <div class="container-fluid mt-4">
        <h2>Chỉnh sửa Quy tắc: {{ $rule->name }}</h2>
        <a href="{{ route('rules.index', ['host' => request('host'), 'shop' => request('shop')]) }}"
            class="btn btn-secondary mb-3">Quay lại</a>

        <form
            action="{{ route('rules.update', ['rule' => $rule->id, 'host' => request('host'), 'shop' => request('shop')]) }}"
            method="POST" class="card p-4">
            @csrf
            @method('PUT')

            @php
                // Giải mã JSON để tránh lỗi in_array()
                $applyTargets = is_array($rule->apply_to_targets)
                    ? $rule->apply_to_targets
                    : (json_decode($rule->apply_to_targets, true) ?? []);
                $excludeProducts = is_array($rule->exclude_products)
                    ? $rule->exclude_products
                    : (json_decode($rule->exclude_products, true) ?? []);
            @endphp

            <div class="row g-3">
                <!-- Tên quy tắc -->
                <div class="form-group col-md-6">
                    <label class="form-label">Tên Quy tắc <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name) }}" required>
                </div>

                <!-- Giá trị giảm giá -->
                <div class="form-group col-md-3">
                    <label class="form-label">Giá trị Giảm giá <span class="text-danger">*</span></label>
                    <input type="number" name="discount_value" class="form-control" step="0.01" min="0"
                        value="{{ old('discount_value', $rule->discount_value) }}" required>
                </div>

                <!-- Loại giảm giá -->
                <div class="form-group col-md-3">
                    <label class="form-label">Loại Giảm giá <span class="text-danger">*</span></label>
                    <select name="discount_type" class="form-select" required>
                        <option value="percentage" {{ old('discount_type', $rule->discount_type) === 'percentage' ? 'selected' : '' }}>Phần trăm (%)</option>
                        <option value="fixed_amount" {{ old('discount_type', $rule->discount_type) === 'fixed_amount' ? 'selected' : '' }}>Số tiền cố định</option>
                    </select>
                </div>

                <!-- Cơ sở giảm giá -->
                <div class="form-group col-md-6">
                    <label class="form-label">Cơ sở Giảm giá <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input type="radio" name="discount_base" value="current_price" id="current_price"
                            class="form-check-input" {{ old('discount_base', $rule->discount_base) === 'current_price' ? 'checked' : '' }}>
                        <label class="form-check-label" for="current_price">Giá hiện tại</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="discount_base" value="compare_at_price" id="compare_at_price"
                            class="form-check-input" {{ old('discount_base', $rule->discount_base) === 'compare_at_price' ? 'checked' : '' }}>
                        <label class="form-check-label" for="compare_at_price">Giá so sánh</label>
                    </div>
                </div>

                <!-- Áp dụng cho -->
                <div class="form-group col-md-6">
                    <label class="form-label">Áp dụng cho <span class="text-danger">*</span></label>
                    <select name="apply_to_type" id="apply_to_type" class="form-select" required>
                        <option value="products" {{ old('apply_to_type', $rule->apply_to_type) === 'products' ? 'selected' : '' }}>Sản phẩm và biến thể</option>
                        <option value="collections" {{ old('apply_to_type', $rule->apply_to_type) === 'collections' ? 'selected' : '' }}>Bộ sưu tập</option>
                        <option value="tags" {{ old('apply_to_type', $rule->apply_to_type) === 'tags' ? 'selected' : '' }}>Thẻ</option>
                        <option value="vendors" {{ old('apply_to_type', $rule->apply_to_type) === 'vendors' ? 'selected' : '' }}>Nhà cung cấp</option>
                        <option value="whole_store" {{ old('apply_to_type', $rule->apply_to_type) === 'whole_store' ? 'selected' : '' }}>Toàn cửa hàng</option>
                    </select>
                </div>

                <!-- Products -->
                <div class="form-group col-12 target-section" id="products-section">
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

                <!-- Collections -->
                <div class="form-group col-12 target-section" id="collections-section">
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

                <!-- Tags -->
                <div class="form-group col-12 target-section" id="tags-section">
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

                <!-- Vendors -->
                <div class="form-group col-12 target-section" id="vendors-section">
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

                <!-- Sản phẩm loại trừ -->
                <div class="form-group col-12">
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

                <!-- Thời gian -->
                <div class="form-group col-md-6">
                    <label class="form-label">Thời gian bắt đầu</label>
                    <input type="datetime-local" name="start_at" class="form-control datetime-picker"
                        value="{{ old('start_at', optional($rule->start_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">Thời gian kết thúc</label>
                    <input type="datetime-local" name="end_at" class="form-control datetime-picker"
                        value="{{ old('end_at', optional($rule->end_at)->format('Y-m-d\TH:i')) }}">
                </div>

                <!-- Thẻ thêm -->
                <div class="form-group col-12">
                    <label class="form-label">Thêm thẻ khi áp dụng, xóa khi vô hiệu hóa</label>
                    <input type="text" name="tags_to_add" class="form-control"
                        value="{{ old('tags_to_add', is_array($rule->tags_to_add) ? implode(', ', $rule->tags_to_add) : $rule->tags_to_add) }}"
                        placeholder="Nhập thẻ, cách nhau bằng dấu phẩy (ví dụ: sale, discount)">
                </div>

                <!-- Submit -->
                <div class="form-group col-12 text-end">
                    <button type="submit" class="btn btn-primary">Cập nhật Quy tắc</button>
                </div>
            </div>
        </form>
    </div>

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
                $('#' + $(this).val() + '-section').addClass('active');
            }).trigger('change');
        });

        $('#ruleForm').on('submit', function (e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    if (res.success) {
                        alert('Cập nhật thành công!');
                        // Reload bảng dữ liệu (AJAX hoặc window.location.reload())
                        loadRulesTable(); // ví dụ hàm bạn tự định nghĩa
                    }
                },
                error: function (err) {
                    alert('Có lỗi xảy ra!');
                }
            });
        });

    </script>
</body>

</html>
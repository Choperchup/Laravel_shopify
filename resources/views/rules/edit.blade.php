<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Quy tắc</title>
    <!-- Thêm Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Thêm Select2 CSS cho multi-select và tìm kiếm -->
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
        <!-- Tiêu đề -->
        <h2>Chỉnh sửa Quy tắc: {{ $rule->name }}</h2>
        <a href="{{ route('rules.index') }}" class="btn btn-secondary mb-3">Quay lại</a>

        <!-- Biểu mẫu chỉnh sửa quy tắc -->
        <form action="{{ route('rules.update', $rule) }}" method="POST" class="card p-4">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <!-- Tên quy tắc -->
                <div class="form-group col-md-6">
                    <label for="name" class="form-label">Tên Quy tắc <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $rule->name) }}"
                        required>
                </div>

                <!-- Giá trị giảm giá -->
                <div class="form-group col-md-3">
                    <label for="discount_value" class="form-label">Giá trị Giảm giá <span
                            class="text-danger">*</span></label>
                    <input type="number" name="discount_value" id="discount_value" class="form-control" step="0.01"
                        min="0" value="{{ old('discount_value', $rule->discount_value) }}" required>
                </div>

                <!-- Loại giảm giá -->
                <div class="form-group col-md-3">
                    <label for="discount_type" class="form-label">Loại Giảm giá <span
                            class="text-danger">*</span></label>
                    <select name="discount_type" id="discount_type" class="form-select" required>
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
                        <label for="current_price" class="form-check-label">Giá hiện tại</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="discount_base" value="compare_at_price" id="compare_at_price"
                            class="form-check-input" {{ old('discount_base', $rule->discount_base) === 'compare_at_price' ? 'checked' : '' }}>
                        <label for="compare_at_price" class="form-check-label">Giá so sánh</label>
                    </div>
                </div>

                <!-- Đối tượng áp dụng -->
                <div class="form-group col-md-6">
                    <label for="apply_to_type" class="form-label">Áp dụng cho <span class="text-danger">*</span></label>
                    <select name="apply_to_type" id="apply_to_type" class="form-select" required>
                        <option value="products" {{ old('apply_to_type', $rule->apply_to_type) === 'products' ? 'selected' : '' }}>Sản phẩm và biến thể</option>
                        <option value="collections" {{ old('apply_to_type', $rule->apply_to_type) === 'collections' ? 'selected' : '' }}>Bộ sưu tập</option>
                        <option value="tags" {{ old('apply_to_type', $rule->apply_to_type) === 'tags' ? 'selected' : '' }}>Thẻ</option>
                        <option value="vendors" {{ old('apply_to_type', $rule->apply_to_type) === 'vendors' ? 'selected' : '' }}>Nhà cung cấp</option>
                        <option value="whole_store" {{ old('apply_to_type', $rule->apply_to_type) === 'whole_store' ? 'selected' : '' }}>Toàn cửa hàng</option>
                    </select>
                </div>

                <!-- Danh sách đối tượng đã chọn -->
                <div class="form-group col-12 target-section" id="products-section">
                    <label class="form-label">Chọn Sản phẩm</label>
                    <select name="apply_to_targets[]" id="products-select" class="form-control select2"
                        multiple="multiple" style="width: 100%;">
                        @foreach ($products as $product)
                            <option value="{{ $product['id'] }}" {{ in_array($product['id'], old('apply_to_targets', $rule->apply_to_targets ?? [])) ? 'selected' : '' }}>
                                {{ $product['title'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 target-section" id="collections-section">
                    <label class="form-label">Chọn Bộ sưu tập</label>
                    <select name="apply_to_targets[]" id="collections-select" class="form-control select2"
                        multiple="multiple" style="width: 100%;">
                        @foreach ($collections as $collection)
                            <option value="{{ $collection['id'] }}" {{ in_array($collection['id'], old('apply_to_targets', $rule->apply_to_targets ?? [])) ? 'selected' : '' }}>
                                {{ $collection['title'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 target-section" id="tags-section">
                    <label class="form-label">Chọn Thẻ</label>
                    <select name="apply_to_targets[]" id="tags-select" class="form-control select2" multiple="multiple"
                        style="width: 100%;">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag['node'] }}" {{ in_array($tag['node'], old('apply_to_targets', $rule->apply_to_targets ?? [])) ? 'selected' : '' }}>
                                {{ $tag['node'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 target-section" id="vendors-section">
                    <label class="form-label">Chọn Nhà cung cấp</label>
                    <select name="apply_to_targets[]" id="vendors-select" class="form-control select2"
                        multiple="multiple" style="width: 100%;">
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor['node'] }}" {{ in_array($vendor['node'], old('apply_to_targets', $rule->apply_to_targets ?? [])) ? 'selected' : '' }}>
                                {{ $vendor['node'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sản phẩm loại trừ -->
                <div class="form-group col-12">
                    <label class="form-label">Sản phẩm loại trừ</label>
                    <select name="exclude_products[]" id="exclude-products" class="form-control select2"
                        multiple="multiple" style="width: 100%;">
                        @foreach ($products as $product)
                            <option value="{{ $product['id'] }}" {{ in_array($product['id'], old('exclude_products', $rule->exclude_products ?? [])) ? 'selected' : '' }}>
                                {{ $product['title'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Thời gian bắt đầu và kết thúc -->
                <div class="form-group col-md-6">
                    <label for="start_at" class="form-label">Thời gian bắt đầu</label>
                    <input type="datetime-local" name="start_at" id="start_at" class="form-control datetime-picker"
                        value="{{ old('start_at', $rule->start_at ? $rule->start_at->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="end_at" class="form-label">Thời gian kết thúc</label>
                    <input type="datetime-local" name="end_at" id="end_at" class="form-control datetime-picker"
                        value="{{ old('end_at', $rule->end_at ? $rule->end_at->format('Y-m-d\TH:i') : '') }}">
                </div>

                <!-- Thẻ thêm/khi áp dụng -->
                <div class="form-group col-12">
                    <label for="tags_to_add" class="form-label">Thêm thẻ khi áp dụng, xóa khi vô hiệu hóa</label>
                    <input type="text" name="tags_to_add" id="tags_to_add" class="form-control"
                        value="{{ old('tags_to_add', implode(', ', $rule->tags_to_add ?? [])) }}"
                        placeholder="Nhập thẻ, cách nhau bằng dấu phẩy (ví dụ: sale, discount)">
                </div>

                <!-- Nút gửi -->
                <div class="form-group col-12 text-end">
                    <button type="submit" class="btn btn-primary">Cập nhật Quy tắc</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Thêm Bootstrap JS và Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Khởi tạo Select2
            $('.select2').select2({
                ajax: {
                    url: function () {
                        switch ($(this).attr('id')) {
                            case 'products-select': return '{{ route('api.products.search') }}';
                            case 'collections-select': return '{{ route('api.collections.search') }}'; // Cần thêm route
                            case 'tags-select': return '{{ route('api.tags.search') }}'; // Cần thêm route
                            case 'vendors-select': return '{{ route('api.vendors.search') }}'; // Cần thêm route
                            case 'exclude-products': return '{{ route('api.products.search') }}';
                        }
                    },
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term, page: params.page };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.map(item => ({ id: item.id, text: item.title || item.node })),
                            pagination: { more: data.length === 250 }
                        };
                    },
                    cache: true
                },
                placeholder: 'Tìm và chọn...',
                allowClear: true,
                minimumInputLength: 2
            }).val(function () {
                return $(this).data('initial') || [];
            }).trigger('change');

            // Đặt giá trị ban đầu cho Select2
            $('#products-select').data('initial', {{ json_encode(old('apply_to_targets', $rule->apply_to_targets ?? [])) }});
            $('#collections-select').data('initial', {{ json_encode(old('apply_to_targets', $rule->apply_to_targets ?? [])) }});
            $('#tags-select').data('initial', {{ json_encode(old('apply_to_targets', $rule->apply_to_targets ?? [])) }});
            $('#vendors-select').data('initial', {{ json_encode(old('apply_to_targets', $rule->apply_to_targets ?? [])) }});
            $('#exclude-products').data('initial', {{ json_encode(old('exclude_products', $rule->exclude_products ?? [])) }});

            // Hiển thị section tương ứng với apply_to_type
            $('#apply_to_type').on('change', function () {
                $('.target-section').removeClass('active');
                $('#' + $(this).val() + '-section').addClass('active');
            }).trigger('change');

            // Đảm bảo start_at < end_at
            $('#start_at, #end_at').on('change', function () {
                const start = new Date($('#start_at').val());
                const end = new Date($('#end_at').val());
                if (start > end && $('#end_at').val()) {
                    alert('Thời gian bắt đầu phải trước thời gian kết thúc!');
                    $('#end_at').val('');
                }
            });
        });
    </script>
</body>

</html>
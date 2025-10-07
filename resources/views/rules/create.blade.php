<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Tạo Quy tắc Mới</title>
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
    <!-- Thêm jQuery trước Select2 và script custom -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container-fluid mt-4">
        <!-- Tiêu đề -->
        <h2>Tạo Quy tắc Mới</h2>
        <a href="{{ route('rules.index') }}?host={{ request()->get('host') }}&shop={{ request()->get('shop') }}"
            class="btn btn-secondary mb-3">Quay lại</a>

        <!-- Biểu mẫu tạo quy tắc -->
        <form action="{{ route('rules.store') }}?host={{ request()->get('host') }}&shop={{ request()->get('shop') }}"
            method="POST" class="card p-4">
            @csrf
            <div class="row g-3">
                <!-- Tên quy tắc -->
                <div class="form-group col-md-6">
                    <label for="name" class="form-label">Tên Quy tắc <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <!-- Giá trị giảm giá -->
                <div class="form-group col-md-3">
                    <label for="discount_value" class="form-label">Giá trị Giảm giá <span
                            class="text-danger">*</span></label>
                    <input type="number" name="discount_value" id="discount_value" class="form-control" step="0.01"
                        min="0" required>
                </div>

                <!-- Loại giảm giá -->
                <div class="form-group col-md-3">
                    <label for="discount_type" class="form-label">Loại Giảm giá <span
                            class="text-danger">*</span></label>
                    <select name="discount_type" id="discount_type" class="form-select" required>
                        <option value="percentage">Phần trăm (%)</option>
                        <option value="fixed_amount">Số tiền cố định</option>
                    </select>
                </div>

                <!-- Cơ sở giảm giá -->
                <div class="form-group col-md-6">
                    <label class="form-label">Cơ sở Giảm giá <span class="text-danger">*</span></label>
                    <div class="form-check">
                        <input type="radio" name="discount_base" value="current_price" id="current_price"
                            class="form-check-input" checked>
                        <label for="current_price" class="form-check-label">Giá hiện tại</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="discount_base" value="compare_at_price" id="compare_at_price"
                            class="form-check-input">
                        <label for="compare_at_price" class="form-check-label">Giá so sánh</label>
                    </div>
                </div>

                <!-- Đối tượng áp dụng -->
                <div class="form-group col-md-6">
                    <label for="apply_to_type" class="form-label">Áp dụng cho <span class="text-danger">*</span></label>
                    <select name="apply_to_type" id="apply_to_type" class="form-select" required>
                        <option value="products">Sản phẩm và biến thể</option>
                        <option value="collections">Bộ sưu tập</option>
                        <option value="tags">Thẻ</option>
                        <option value="vendors">Nhà cung cấp</option>
                        <option value="whole_store">Toàn cửa hàng</option>
                    </select>
                </div>

                <!-- Danh sách đối tượng đã chọn -->
                <div class="form-group col-12 target-section" id="products-section">
                    <label class="form-label">Chọn Sản phẩm</label>
                    <select name="apply_to_targets[]" id="products-select" class="form-control select2"
                        multiple="multiple" style="width: 100%;">
                        @forelse ($products as $product)
                            <option value="{{ $product['id'] ?? '' }}" {{ in_array($product['id'] ?? '', old('apply_to_targets', [])) ? 'selected' : '' }}>
                                {{ $product['title'] ?? '' }}
                            </option>
                        @empty
                            <option value="" disabled>Không có sản phẩm</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group col-12 target-section" id="collections-section">
                    <label class="form-label">Chọn Bộ sưu tập</label>
                    <select name="apply_to_targets[]" id="collections-select" class="form-control select2"
                        multiple="multiple" style="width: 100%;">
                        @forelse ($collections as $collection)
                            <option value="{{ $collection['node']['id'] ?? '' }}" {{ in_array($collection['node']['id'] ?? '', old('apply_to_targets', [])) ? 'selected' : '' }}>
                                {{ $collection['node']['title'] ?? '' }}
                            </option>
                        @empty
                            <option value="" disabled>Không có bộ sưu tập</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group col-12 target-section" id="tags-section">
                    <label class="form-label">Chọn Thẻ</label>
                    <select name="apply_to_targets[]" id="tags-select" class="form-control select2" multiple="multiple"
                        style="width: 100%;">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag['node'] ?? '' }}" {{ in_array($tag['node'] ?? '', old('apply_to_targets', [])) ? 'selected' : '' }}>
                                {{ $tag['node'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-12 target-section" id="vendors-section">
                    <label class="form-label">Chọn Nhà cung cấp</label>
                    <select name="apply_to_targets[]" id="vendors-select" class="form-control select2"
                        multiple="multiple" style="width: 100%;">
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor['node'] ?? '' }}" {{ in_array($vendor['node'] ?? '', old('apply_to_targets', [])) ? 'selected' : '' }}>
                                {{ $vendor['node'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sản phẩm loại trừ -->
                <div class="form-group col-12">
                    <label class="form-label">Sản phẩm loại trừ</label>
                    <select name="exclude_products[]" id="exclude-products" class="form-control select2"
                        multiple="multiple" style="width: 100%;">
                        @forelse ($products as $product)
                            <option value="{{ $product['id'] ?? '' }}" {{ in_array($product['id'] ?? '', old('exclude_products', [])) ? 'selected' : '' }}>
                                {{ $product['title'] ?? '' }}
                            </option>
                        @empty
                            <option value="" disabled>Không có sản phẩm</option>
                        @endforelse
                    </select>
                </div>

                <!-- Thời gian bắt đầu và kết thúc -->
                <div class="form-group col-md-6">
                    <label for="start_at" class="form-label">Thời gian bắt đầu</label>
                    <input type="datetime-local" name="start_at" id="start_at" class="form-control datetime-picker">
                </div>
                <div class="form-group col-md-6">
                    <label for="end_at" class="form-label">Thời gian kết thúc</label>
                    <input type="datetime-local" name="end_at" id="end_at" class="form-control datetime-picker">
                </div>

                <!-- Thẻ thêm/khi áp dụng -->
                <div class="form-group col-12">
                    <label for="tags_to_add" class="form-label">Thêm thẻ khi áp dụng, xóa khi vô hiệu hóa</label>
                    <input type="text" name="tags_to_add" id="tags_to_add" class="form-control"
                        placeholder="Nhập thẻ, cách nhau bằng dấu phẩy (ví dụ: sale, discount)">
                </div>

                <!-- Nút gửi -->
                <div class="form-group col-12 text-end">
                    <button type="submit" class="btn btn-primary">Lưu Quy tắc</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Thêm Bootstrap JS và Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Extract host and shop from current URL
            var urlParams = new URLSearchParams(window.location.search);
            var hostParam = urlParams.get('host');
            var shopParam = urlParams.get('shop');

            // Khởi tạo Select2 với AJAX
            $('.select2').select2({
                ajax: {
                    url: function () {
                        var baseUrl;
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
                        // Append host and shop parameters
                        if (hostParam && shopParam) {
                            baseUrl += (baseUrl.includes('?') ? '&' : '?') + 'host=' + encodeURIComponent(hostParam) + '&shop=' + encodeURIComponent(shopParam);
                        }
                        return baseUrl;
                    },
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page,
                            host: hostParam, // Gửi host trong data
                            shop: shopParam  // Gửi shop trong data
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.map(item => ({ id: item.id, text: item.text || item.title || item.node })),
                            pagination: { more: data.length === 250 }
                        };
                    },
                    cache: true
                },
                placeholder: 'Tìm và chọn...',
                allowClear: true,
                minimumInputLength: 2
            });

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

            // Xử lý submit form qua AJAX
            $('form').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const url = form.attr('action');
                const data = form.serialize() + (hostParam && shopParam ? '&host=' + encodeURIComponent(hostParam) + '&shop=' + encodeURIComponent(shopParam) : '');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(response.message || "Lưu thành công!");
                            form.trigger("reset");
                            $('.select2').val(null).trigger('change');
                        } else {
                            alert(response.message || 'Lưu thất bại!');
                        }
                    },
                    error: function (xhr) {
                        alert('Lỗi: ' + (xhr.responseJSON?.message || 'Vui lòng thử lại'));
                    }
                });
            });
        });
    </script>
</body>

</html>
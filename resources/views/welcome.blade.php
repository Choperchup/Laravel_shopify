<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Shopify Products</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f9fafb;
            color: #111827;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .space-x-4>*+* {
            margin-left: 1rem;
        }

        .space-y-4>*+* {
            margin-top: 1rem;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .font-medium {
            font-weight: 500;
        }

        .border {
            border: 1px solid #d1d5db;
        }

        .rounded {
            border-radius: 0.375rem;
        }

        .px-3 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .py-8 {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .mb-6 {
            margin-bottom: 1.5rem;
        }

        .grid {
            display: grid;
        }

        .grid-cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .gap-6 {
            gap: 1.5rem;
        }

        .bg-white {
            background-color: white;
        }

        .rounded-lg {
            border-radius: 0.5rem;
        }

        .overflow-hidden {
            overflow: hidden;
        }

        .p-4 {
            padding: 1rem;
        }

        .aspect-square {
            aspect-ratio: 1 / 1;
        }

        .object-cover {
            object-fit: cover;
        }

        .w-full {
            width: 100%;
        }

        .h-full {
            height: 100%;
        }

        .text-center {
            text-align: center;
        }

        .py-12 {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        .hidden {
            display: none;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .mt-8 {
            margin-top: 2rem;
        }

        .shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .transition-shadow {
            transition: box-shadow 0.15s ease-in-out;
        }

        .hover-shadow:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .text-green-600 {
            color: #059669;
        }

        .text-gray-600 {
            color: #4b5563;
        }

        .bg-gray-100 {
            background-color: #f3f4f6;
        }

        .text-gray-400 {
            color: #9ca3af;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mt-2 {
            margin-top: 0.5rem;
        }

        .text-xs {
            font-size: 0.75rem;
        }

        .px-2 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .py-1 {
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }

        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .bg-red-50 {
            background-color: #fef2f2;
        }

        .border-red-200 {
            border-color: #fecaca;
        }

        .text-red-800 {
            color: #991b1b;
        }

        .text-red-700 {
            color: #b91c1c;
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* Thêm style cho bảng */
        .table-container {
            overflow-x: auto;
            /* Cho phép scroll ngang nếu bảng rộng */
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        .table th,
        .table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .table th {
            background-color: #f9fafb;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #4b5563;
        }

        .table td {
            font-size: 0.875rem;
        }

        .table .product-cell {
            display: flex;
            align-items: center;
        }

        .table .product-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            margin-right: 1rem;
            border-radius: 0.25rem;
        }

        .table .status-active {
            color: #059669;
            background-color: #d1fae5;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
        }

        .table .channels {
            text-align: right;
        }

        /* Giữ nguyên các style khác */

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .grid-cols-4 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    @php
        $shops = $shops ?? collect();
        $selectedShop = $selectedShop ?? null;
        $products = $products ?? [];
        $pageInfo = $pageInfo ?? null;
        $perPage = $perPage ?? 10;
        $error = $error ?? null;
        $filters = $filters ?? [];
        $sort = $sort ?? ['field' => 'title', 'direction' => 'ASC'];
        $collections = $collections ?? [];
        $vendors = $vendors ?? [];
        $productTypes = $productTypes ?? [];
        $tags = $tags ?? [];
    @endphp
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-medium">Shopify Products</h1>
                    @if(isset($selectedShop))
                        <div class="text-sm text-gray-600">
                            Shop: {{ $selectedShop->email ?: str_replace('.myshopify.com', '', $selectedShop->name) }}
                        </div>
                    @endif
                </div>

                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm">Hiển thị:</span>
                        <select id="per-page-selector" class="border rounded px-3 py-2 text-sm">
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                            <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250</option>
                        </select>
                    </div>
                    <button id="refresh-btn" class="border rounded px-3 py-2 text-sm font-medium">Làm mới</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Filters -->
    <section class="container py-4">
        <div class="bg-white rounded-lg border p-4 space-y-4">
            <div class="grid grid-cols-4 gap-4">
                <!-- Title Filter -->
                <div>
                    <label for="title-filter" class="text-sm font-medium">Tiêu đề</label>
                    <input type="text" id="title-filter" class="border rounded px-3 py-2 text-sm w-full"
                        value="{{ $filters['title'] ?? '' }}" placeholder="Nhập tiêu đề...">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status-filter" class="text-sm font-medium">Trạng thái</label>
                    <select id="status-filter" class="border rounded px-3 py-2 text-sm w-full">
                        <option value="" {{ empty($filters['status']) ? 'selected' : '' }}>Tất cả</option>
                        <option value="ACTIVE" {{ $filters['status'] == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="DRAFT" {{ $filters['status'] == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="ARCHIVED" {{ $filters['status'] == 'ARCHIVED' ? 'selected' : '' }}>Archive</option>
                    </select>
                </div>

                <!-- Tag Filter -->
                <div>
                    <label for="tag-filter" class="text-sm font-medium">Tag</label>
                    <select id="tag-filter" class="border rounded px-3 py-2 text-sm w-full">
                        <option value="" {{ empty($filters['tag']) ? 'selected' : '' }}>Chọn tag</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag }}" {{ $filters['tag'] == $tag ? 'selected' : '' }}>{{ $tag }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Collection Filter -->
                <div>
                    <label for="collection-filter" class="text-sm font-medium">Bộ sưu tập</label>
                    <select id="collection-filter" class="border rounded px-3 py-2 text-sm w-full">
                        <option value="" {{ empty($filters['collection']) ? 'selected' : '' }}>Chọn bộ sưu tập</option>
                        @foreach($collections as $collection)
                            <option value="{{ $collection['id'] }}" {{ $filters['collection'] == $collection['id'] ? 'selected' : '' }}>{{ $collection['title'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Vendor Filter -->
            <div>
                <label for="vendor-filter" class="text-sm font-medium">Nhà cung cấp</label>
                <select id="vendor-filter" multiple class="border rounded px-3 py-2 text-sm w-full">
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor }}" {{ in_array($vendor, $filters['vendors'] ?? []) ? 'selected' : '' }}>
                            {{ $vendor }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Product Type Filter -->
            <div>
                <label for="product-type-filter" class="text-sm font-medium">Loại sản phẩm</label>
                <select id="product-type-filter" multiple class="border rounded px-3 py-2 text-sm w-full">
                    @foreach($productTypes as $type)
                        <option value="{{ $type }}" {{ in_array($type, $filters['productTypes'] ?? []) ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="sort-field" class="text-sm font-medium">Sắp xếp theo</label>
                    <select id="sort-field" class="border rounded px-3 py-2 text-sm w-full">
                        <option value="title" {{ $sort['field'] == 'title' ? 'selected' : '' }}>Tiêu đề</option>
                        <option value="createdAt" {{ $sort['field'] == 'createdAt' ? 'selected' : '' }}>Ngày tạo</option>
                        <option value="updatedAt" {{ $sort['field'] == 'updatedAt' ? 'selected' : '' }}>Ngày cập nhật
                        </option>
                        <option value="productType" {{ $sort['field'] == 'productType' ? 'selected' : '' }}>Loại sản phẩm
                        </option>
                        <option value="vendor" {{ $sort['field'] == 'vendor' ? 'selected' : '' }}>Nhà cung cấp</option>
                    </select>
                </div>
                <div>
                    <label for="sort-direction" class="text-sm font-medium">Hướng</label>
                    <select id="sort-direction" class="border rounded px-3 py-2 text-sm w-full">
                        <option value="ASC" {{ $sort['direction'] == 'ASC' ? 'selected' : '' }}>Tăng dần</option>
                        <option value="DESC" {{ $sort['direction'] == 'DESC' ? 'selected' : '' }}>Giảm dần</option>
                    </select>
                </div>
            </div>

            <button id="apply-filters" class="border rounded px-4 py-2 text-sm font-medium bg-blue-600 text-white">Áp
                dụng</button>
        </div>

        <!-- Bulk Actions Section (Thêm để hỗ trợ bulk actions) -->
        <div class="bg-white rounded-lg border p-4 mt-4">
            <h3 class="text-sm font-medium mb-2">Bulk Actions</h3>
            <div class="flex space-x-4">
                <select id="bulk-action" class="border rounded px-3 py-2 text-sm">
                    <option value="">Chọn hành động</option>
                    <option value="active">Active Products</option>
                    <option value="draft">Draft Products</option>
                    <option value="archive">Archive Products</option>
                    <option value="add_tags">Add Tags</option>
                    <option value="remove_tags">Remove Tags</option>
                    <option value="add_collection">Add to Collection</option>
                    <option value="remove_collection">Remove from Collection</option>
                </select>
                <input type="text" id="bulk-tags" placeholder="Tags (comma separated)"
                    class="border rounded px-3 py-2 text-sm hidden" style="width: 200px;">
                <select id="bulk-collection" class="border rounded px-3 py-2 text-sm hidden">
                    <option value="">Chọn collection</option>
                    @foreach($collections as $collection)
                        <option value="{{ $collection['id'] }}">{{ $collection['title'] }}</option>
                    @endforeach
                </select>
                <button id="bulk-submit"
                    class="border rounded px-4 py-2 text-sm font-medium bg-green-600 text-white">Thực hiện</button>
            </div>
            <p id="bulk-status" class="text-sm text-gray-600 mt-2"></p>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container py-8">
        <!-- Error Message -->
        @if(isset($error) && $error)
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="text-sm font-medium text-red-800">Lỗi khi tải sản phẩm</h3>
                <div class="mt-2 text-sm text-red-700">{{ is_array($error) ? json_encode($error) : $error }}</div>
            </div>
        @endif

        <!-- Loading State -->
        <div id="loading" class="hidden text-center py-12">
            <div class="animate-spin rounded border-2 border-blue-600"
                style="width: 2rem; height: 2rem; margin: 0 auto; border-top-color: transparent;"></div>
            <span class="text-sm text-gray-600 mt-2" style="display: block;">Đang tải sản phẩm...</span>
        </div>

        <!-- Products Grid -->
        <div id="products-container">
            @if(isset($selectedShop) && $selectedShop && isset($products) && count($products) > 0)
                <div class="mb-6 text-sm text-gray-600">
                    Đang hiển thị sản phẩm từ: <span
                        class="font-medium">{{ $selectedShop->email ?: str_replace('.myshopify.com', '', $selectedShop->name) }}</span>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all" class="border rounded"></th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Inventory</th>
                                <th>Category</th>
                                <th>Channels</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td><input type="checkbox" class="product-checkbox border rounded"
                                            data-id="{{ $product['id'] }}"></td>
                                    <td class="product-cell">
                                        @if(isset($product['images']) && count($product['images']) > 0)
                                            <img src="{{ $product['images'][0]['url'] }}"
                                                alt="{{ $product['images'][0]['altText'] ?? $product['title'] }}"
                                                class="product-image">
                                        @else
                                            <div
                                                class="product-image bg-gray-100 flex items-center justify-center text-gray-400 text-xs">
                                                Không có ảnh</div>
                                        @endif
                                        <div>
                                            <p class="font-medium">{{ $product['title'] }}</p>
                                            @if(isset($product['vendor']) && $product['vendor'])
                                                <p class="text-xs text-gray-600">{{ $product['vendor'] }}</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td><span
                                            class="text-xs px-2 py-1 rounded {{ strtolower($product['status']) === 'active' ? 'text-green-600 bg-green-100' : 'text-gray-600 bg-gray-100' }}">{{ $product['status'] }}</span>
                                    </td>
                                    <td>Inventory not tracked</td> <!-- Có thể cập nhật động nếu có data inventory -->
                                    <td>{{ $product['productType'] ?? '' }}</td>
                                    <td class="channels">1</td> <!-- Giả định, có thể cập nhật từ API -->
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(isset($pageInfo) && $pageInfo && ($pageInfo['hasNextPage'] || $pageInfo['hasPreviousPage']))
                    <div class="mt-8 text-center space-x-4">
                        @if($pageInfo['hasPreviousPage'])
                            <button id="prev-page" data-cursor="{{ $pageInfo['startCursor'] }}"
                                class="border rounded px-4 py-2 text-sm font-medium">← Trang trước</button>
                        @endif
                        @if($pageInfo['hasNextPage'])
                            <button id="next-page" data-cursor="{{ $pageInfo['endCursor'] }}"
                                class="border rounded px-4 py-2 text-sm font-medium">Trang sau →</button>
                        @endif
                    </div>
                @endif
            @elseif(isset($selectedShop) && $selectedShop)
                <div class="text-center py-12">
                    <h3 class="text-sm font-medium">Không có sản phẩm</h3>
                    <p class="text-sm text-gray-600">Shop này chưa có sản phẩm nào.</p>
                </div>
            @else
                <div class="text-center py-12">
                    <h3 class="text-sm font-medium">Chưa có shop nào</h3>
                    <p class="text-sm text-gray-600">Vui lòng thêm shop vào hệ thống để hiển thị sản phẩm.</p>
                </div>
            @endif
        </div>
    </main>

    <script>
        let isLoading = false;

        async function fetchProducts(params = {}) {
            if (isLoading) return;

            isLoading = true;
            document.getElementById('loading').classList.remove('hidden');

            try {
                const response = await fetch(`/api/products?${new URLSearchParams(params)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    renderProducts(data.products, data.pageInfo);
                } else {
                    showError(data.error || 'Có lỗi xảy ra khi tải sản phẩm');
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showError('Không thể kết nối đến server');
            } finally {
                isLoading = false;
                document.getElementById('loading').classList.add('hidden');
            }
        }

        function renderProducts(products, pageInfo) {
            const container = document.getElementById('products-container');

            if (products.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <h3 class="text-sm font-medium">Không có sản phẩm</h3>
                        <p class="text-sm text-gray-600">Shop này chưa có sản phẩm nào.</p>
                    </div>
                `;
                return;
            }

            const shopName = document.querySelector('.header .text-gray-600')?.textContent.replace('Shop: ', '') || 'Shop';

            let html = `
                <div class="mb-6 text-sm text-gray-600">
                    Đang hiển thị sản phẩm từ: <span class="font-medium">${shopName}</span>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all" class="border rounded"></th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Inventory</th>
                                <th>Category</th>
                                <th>Channels</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            products.forEach(product => {
                const imageHtml = product.images && product.images.length > 0
                    ? `<img src="${product.images[0].url}" alt="${product.images[0].altText || product.title}" class="product-image">`
                    : `<div class="product-image bg-gray-100 flex items-center justify-center text-gray-400 text-xs">Không có ảnh</div>`;

                const statusClass = product.status.toLowerCase() === 'active' ? 'text-green-600 bg-green-100' : 'text-gray-600 bg-gray-100';

                html += `
                    <tr>
                        <td><input type="checkbox" class="product-checkbox border rounded" data-id="${product.id}"></td>
                        <td class="product-cell">
                            ${imageHtml}
                            <div>
                                <p class="font-medium">${product.title}</p>
                                ${product.vendor ? `<p class="text-xs text-gray-600">${product.vendor}</p>` : ''}
                            </div>
                        </td>
                        <td><span class="text-xs px-2 py-1 rounded ${statusClass}">${product.status}</span></td>
                        <td>Inventory not tracked</td> 
                        <td>${product.productType || ''}</td>
                        <td class="channels">1</td> 
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            if (pageInfo && (pageInfo.hasNextPage || pageInfo.hasPreviousPage)) {
                html += `<div class="mt-8 text-center space-x-4">`;
                if (pageInfo.hasPreviousPage) {
                    html += `<button id="prev-page" data-cursor="${pageInfo.startCursor}" class="border rounded px-4 py-2 text-sm font-medium">← Trang trước</button>`;
                }
                if (pageInfo.hasNextPage) {
                    html += `<button id="next-page" data-cursor="${pageInfo.endCursor}" class="border rounded px-4 py-2 text-sm font-medium">Trang sau →</button>`;
                }
                html += `</div>`;
            }

            container.innerHTML = html;
            bindPaginationEvents();
            bindEvents();
        }

        function bindPaginationEvents() {
            const prevBtn = document.getElementById('prev-page');
            const nextBtn = document.getElementById('next-page');

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    fetchProducts(getFilterParams({ after: prevBtn.dataset.cursor }));
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    fetchProducts(getFilterParams({ after: nextBtn.dataset.cursor }));
                });
            }
        }

        function bindEvents() {
            const selectAll = document.getElementById('select-all');
            const productCheckboxes = document.querySelectorAll('.product-checkbox');

            selectAll.addEventListener('change', () => {
                productCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
            });

            productCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    if (!checkbox.checked) {
                        selectAll.checked = false;
                    } else if (Array.from(productCheckboxes).every(cb => cb.checked)) {
                        selectAll.checked = true;
                    }
                });
            });
        }

        function getFilterParams(override = {}) {
            const params = {
                per_page: document.getElementById('per-page-selector')?.value || 50,
                title: document.getElementById('title-filter')?.value || '',
                status: document.getElementById('status-filter')?.value || '',
                tag: document.getElementById('tag-filter')?.value || '',
                collection: document.getElementById('collection-filter')?.value || '',
                vendors: Array.from(document.getElementById('vendor-filter')?.selectedOptions || []).map(opt => opt.value).join(','),
                productTypes: Array.from(document.getElementById('product-type-filter')?.selectedOptions || []).map(opt => opt.value).join(','),
                sortField: document.getElementById('sort-field')?.value || 'title',
                sortDirection: document.getElementById('sort-direction')?.value || 'ASC',
                ...override
            };
            return params;
        }

        function showError(message) {
            const container = document.getElementById('products-container');
            container.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-red-800">Lỗi khi tải sản phẩm</h3>
                    <div class="mt-2 text-sm text-red-700">${message}</div>
                </div>
            `;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const perPageSelector = document.getElementById('per-page-selector');
            if (perPageSelector) {
                perPageSelector.addEventListener('change', () => fetchProducts(getFilterParams()));
            }

            const refreshBtn = document.getElementById('refresh-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', () => fetchProducts(getFilterParams()));
            }

            const applyFiltersBtn = document.getElementById('apply-filters');
            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', () => fetchProducts(getFilterParams()));
            }

            bindEvents();
            bindPaginationEvents();

            // Thêm event cho Bulk Actions
            const bulkActionSelect = document.getElementById('bulk-action');
            if (bulkActionSelect) {
                bulkActionSelect.addEventListener('change', function () {
                    const action = this.value;
                    document.getElementById('bulk-tags')?.classList.toggle('hidden', !['add_tags', 'remove_tags'].includes(action));
                    document.getElementById('bulk-collection')?.classList.toggle('hidden', !['add_collection', 'remove_collection'].includes(action));
                });
            }

            const bulkSubmitBtn = document.getElementById('bulk-submit');
            if (bulkSubmitBtn) {
                bulkSubmitBtn.addEventListener('click', async function () {
                    const selected = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.dataset.id);
                    if (selected.length === 0) {
                        alert('Vui lòng chọn sản phẩm!');
                        return;
                    }

                    const action = document.getElementById('bulk-action').value;
                    if (!action) {
                        alert('Vui lòng chọn hành động!');
                        return;
                    }

                    let payload = { action, product_ids: selected };

                    if (['add_tags', 'remove_tags'].includes(action)) {
                        payload.tags = document.getElementById('bulk-tags').value.split(',').map(t => t.trim()).filter(t => t);
                        if (payload.tags.length === 0) {
                            alert('Vui lòng nhập tags!');
                            return;
                        }
                    } else if (['add_collection', 'remove_collection'].includes(action)) {
                        payload.collection_id = document.getElementById('bulk-collection').value;
                        if (!payload.collection_id) {
                            alert('Vui lòng chọn collection!');
                            return;
                        }
                    }

                    try {
                        const response = await fetch('/api/products/bulk-action', {  // Điểm cuối đã sửa
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(payload)
                        });

                        if (!response.ok) {
                            const text = await response.text();  // Ghi lại phản hồi đầy đủ để gỡ lỗi
                            console.error('Phản hồi Lỗi API:', text);
                            throw new Error(`Lỗi HTTP! Trạng thái: ${response.status}`);
                        }

                        const data = await response.json();
                        document.getElementById('bulk-status').textContent = data.message;
                        if (data.success) {
                            // Làm mới sản phẩm sau 5 giây
                            setTimeout(() => fetchProducts(getFilterParams()), 5000);
                        } else {
                            alert('Lỗi: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Lỗi Hành động Hàng loạt:', error);
                        alert('Lỗi kết nối: ' + error.message);
                    }
                });
            }
        });
    </script>
</body>

</html>
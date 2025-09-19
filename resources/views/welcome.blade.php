<<<<<<< HEAD
{{-- @extends('shopify-app::layouts.default')

@section('content')
<!-- You are: (shop domain name) -->
<h1>Hello</h1>
<p>You are: {{ $shopDomain ?? Auth::user()->name }}</p>

<ui-title-bar title="Products">
    <button onclick="console.log('Secondary action')">Secondary action</button>
    <button variant="primary" onclick="console.log('Primary action')">
        Primary action
    </button>
</ui-title-bar>
@endsection --}}

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopify App - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #96e6a1, #d4fc79);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: #2c3e50;
        }

        .btn-shopify {
            background-color: #5c6ac4;
            color: white;
        }

        .btn-shopify:hover {
            background-color: #4b59a2;
        }
    </style>
</head>

<body>
    <div class="card p-5 text-center" style="width: 400px;">
        <h1 class="mb-3">Welcome to Your Shopify App</h1>
        <p class="mb-4">Vui lòng đăng ký hoặc đăng nhập để tiếp tục.</p>
        <a href="{{ url('/register') }}" class="btn btn-shopify mb-3 w-100">Đăng ký</a>
        <a href="{{ route('login')}}" class="btn btn-outline-dark w-100">Đăng nhập</a>
    </div>
</body>

=======
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Shopify Products</title>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f9fafb; color: #111827; line-height: 1.5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
        .header { background: white; border-bottom: 1px solid #e5e7eb; padding: 1rem 0; position: sticky; top: 0; z-index: 50; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .space-x-4 > * + * { margin-left: 1rem; }
        .text-xl { font-size: 1.25rem; }
        .font-medium { font-weight: 500; }
        .border { border: 1px solid #d1d5db; }
        .rounded { border-radius: 0.375rem; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .grid { display: grid; }
        .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
        .gap-6 { gap: 1.5rem; }
        .bg-white { background-color: white; }
        .rounded-lg { border-radius: 0.5rem; }
        .overflow-hidden { overflow: hidden; }
        .p-4 { padding: 1rem; }
        .aspect-square { aspect-ratio: 1 / 1; }
        .object-cover { object-fit: cover; }
        .w-full { width: 100%; }
        .h-full { height: 100%; }
        .text-center { text-align: center; }
        .py-12 { padding-top: 3rem; padding-bottom: 3rem; }
        .hidden { display: none; }
        .text-sm { font-size: 0.875rem; }
        .mt-8 { margin-top: 2rem; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .transition-shadow { transition: box-shadow 0.15s ease-in-out; }
        .hover-shadow:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .text-green-600 { color: #059669; }
        .text-gray-600 { color: #4b5563; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .text-gray-400 { color: #9ca3af; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mt-2 { margin-top: 0.5rem; }
        .text-xs { font-size: 0.75rem; }
        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .bg-red-50 { background-color: #fef2f2; }
        .border-red-200 { border-color: #fecaca; }
        .text-red-800 { color: #991b1b; }
        .text-red-700 { color: #b91c1c; }
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        @media (max-width: 768px) { .grid-cols-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .grid-cols-4 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    @php
        $shops = $shops ?? collect();
        $selectedShop = $selectedShop ?? null;
        $products = $products ?? [];
        $pageInfo = $pageInfo ?? null;
        $perPage = $perPage ?? 50;
        $error = $error ?? null;
    @endphp
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-medium">Shopify Products</h1>
                    
                    {{-- Ẩn dropdown shop selector vì bảo mật - chỉ hiển thị shop hiện tại --}}
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
            <div class="animate-spin rounded border-2 border-blue-600" style="width: 2rem; height: 2rem; margin: 0 auto; border-top-color: transparent;"></div>
            <span class="text-sm text-gray-600 mt-2" style="display: block;">Đang tải sản phẩm...</span>
        </div>

        <!-- Products Grid -->
        <div id="products-container">
            @if(isset($selectedShop) && $selectedShop && isset($products) && count($products) > 0)
                <div class="mb-6 text-sm text-gray-600">
                    Đang hiển thị sản phẩm từ: <span class="font-medium">{{ $selectedShop->email ?: str_replace('.myshopify.com', '', $selectedShop->name) }}</span>
                </div>

                <div class="grid grid-cols-4 gap-6" id="products-grid">
                    @foreach($products as $product)
                        <div class="bg-white rounded-lg border overflow-hidden hover-shadow transition-shadow">
                            <!-- Product Image -->
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                @if(isset($product['images']) && count($product['images']) > 0)
                                    <img src="{{ $product['images'][0]['url'] }}" 
                                         alt="{{ $product['images'][0]['altText'] ?? $product['title'] }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        Không có ảnh
                                    </div>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="p-4">
                                <h3 class="font-medium mb-2 line-clamp-2">{{ $product['title'] }}</h3>
                                
                                @if(isset($product['vendor']) && $product['vendor'])
                                    <p class="text-xs text-gray-600 mb-2">{{ $product['vendor'] }}</p>
                                @endif

                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-medium">
                                        @if($product['priceRange']['minVariantPrice']['amount'] === $product['priceRange']['maxVariantPrice']['amount'])
                                            {{ number_format($product['priceRange']['minVariantPrice']['amount'], 0, ',', '.') }} {{ $product['priceRange']['minVariantPrice']['currencyCode'] }}
                                        @else
                                            {{ number_format($product['priceRange']['minVariantPrice']['amount'], 0, ',', '.') }} - {{ number_format($product['priceRange']['maxVariantPrice']['amount'], 0, ',', '.') }} {{ $product['priceRange']['minVariantPrice']['currencyCode'] }}
                                        @endif
                                    </div>
                                    
                                    <span class="text-xs px-2 py-1 rounded {{ $product['status'] === 'ACTIVE' ? 'text-green-600' : 'text-gray-600' }}">
                                        {{ $product['status'] }}
                                    </span>
                                </div>

                                @if(isset($product['variants']) && count($product['variants']) > 1)
                                    <p class="text-xs text-gray-600 mt-2">{{ count($product['variants']) }} biến thể</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
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

        async function fetchProducts(shop, perPage, cursor = null) {
            if (isLoading) return;
            
            isLoading = true;
            document.getElementById('loading').classList.remove('hidden');
            
            try {
                const params = new URLSearchParams({
                    per_page: perPage
                });
                
                if (cursor) {
                    params.append('after', cursor);
                }

                const response = await fetch(`/api/products?${params}`, {
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

            const shopSelector = document.getElementById('shop-selector');
            const shopName = shopSelector ? shopSelector.selectedOptions[0].text : 'Shop';

            let html = `
                <div class="mb-6 text-sm text-gray-600">
                    Đang hiển thị sản phẩm từ: <span class="font-medium">${shopName}</span>
                </div>
                <div class="grid grid-cols-4 gap-6">
            `;

            products.forEach(product => {
                const imageHtml = product.images && product.images.length > 0 
                    ? `<img src="${product.images[0].url}" alt="${product.images[0].altText || product.title}" class="w-full h-full object-cover">`
                    : `<div class="w-full h-full flex items-center justify-center text-gray-400">Không có ảnh</div>`;

                const priceHtml = product.priceRange.minVariantPrice.amount === product.priceRange.maxVariantPrice.amount
                    ? `${Number(product.priceRange.minVariantPrice.amount).toLocaleString()} ${product.priceRange.minVariantPrice.currencyCode}`
                    : `${Number(product.priceRange.minVariantPrice.amount).toLocaleString()} - ${Number(product.priceRange.maxVariantPrice.amount).toLocaleString()} ${product.priceRange.minVariantPrice.currencyCode}`;

                const statusClass = product.status === 'ACTIVE' ? 'text-green-600' : 'text-gray-600';

                html += `
                    <div class="bg-white rounded-lg border overflow-hidden hover-shadow transition-shadow">
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            ${imageHtml}
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium mb-2 line-clamp-2">${product.title}</h3>
                            ${product.vendor ? `<p class="text-xs text-gray-600 mb-2">${product.vendor}</p>` : ''}
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium">${priceHtml}</div>
                                <span class="text-xs px-2 py-1 rounded ${statusClass}">${product.status}</span>
                            </div>
                            ${product.variants && product.variants.length > 1 ? `<p class="text-xs text-gray-600 mt-2">${product.variants.length} biến thể</p>` : ''}
                        </div>
                    </div>
                `;
            });

            html += '</div>';

            // Add pagination
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
        }

        function bindPaginationEvents() {
            const prevBtn = document.getElementById('prev-page');
            const nextBtn = document.getElementById('next-page');
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    const perPage = document.getElementById('per-page-selector').value;
                    fetchProducts('', perPage, prevBtn.dataset.cursor);
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    const perPage = document.getElementById('per-page-selector').value;
                    fetchProducts('', perPage, nextBtn.dataset.cursor);
                });
            }
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

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Shop selector đã bị loại bỏ vì lý do bảo mật

            const perPageSelector = document.getElementById('per-page-selector');
            if (perPageSelector) {
                perPageSelector.addEventListener('change', function() {
                    const perPage = this.value;
                    fetchProducts('', perPage);
                });
            }

            const refreshBtn = document.getElementById('refresh-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    const perPage = document.getElementById('per-page-selector').value;
                    fetchProducts('', perPage);
                });
            }

            bindPaginationEvents();
        });
    </script>
</body>
>>>>>>> e44920f (GraphQL Product)
</html>
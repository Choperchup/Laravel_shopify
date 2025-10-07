<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'My Shopify App' }}</title>
</head>

<body>
    {{-- Navbar --}}
    <nav style="padding: 10px; background: #f4f6f8; border-bottom: 1px solid #ddd;">
        <a href="{{ route('home', ['host' => request('host'), 'shop' => request('shop')]) }}" data-redirect
            style="margin-right: 15px;">Home</a>
        <a href="{{ route('product.index', ['host' => request('host'), 'shop' => request('shop')]) }}" data-redirect
            style="margin-right: 15px;">Products</a>
        <a href="{{ route('orders.index', ['host' => request('host'), 'shop' => request('shop')]) }}" data-redirect
            style="margin-right: 15px;">Orders</a>
        <a href="{{ route('rules.index', ['host' => request('host'), 'shop' => request('shop')]) }}" data-redirect
            style="margin-right: 15px;">Rules</a>
    </nav>

    {{-- Nội dung trang --}}
    <div style="padding: 20px;">
        @yield('content')
    </div>

    {{-- Shopify App Bridge CDN --}}
    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
    <script src="https://unpkg.com/@shopify/app-bridge-utils@3"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var AppBridge = window['app-bridge'];
            var AppBridgeUtils = window['app-bridge-utils'];

            var createApp = AppBridge.default;
            var actions = AppBridge.actions;
            var getSessionToken = AppBridgeUtils.getSessionToken;

            var host = new URLSearchParams(window.location.search).get("host");

            var app = createApp({
                apiKey: "{{ config('shopify-app.api_key') }}",
                host: host,
                forceRedirect: false, // Chuyển sang false để tự xử lý redirect
            });

            // TitleBar
            var TitleBar = actions.TitleBar;
            TitleBar.create(app, { title: "{{ $title ?? 'Dashboard' }}" });

            // Toast ví dụ
            var Toast = actions.Toast;
            var toast = Toast.create(app, { message: "Xin chào từ Blade + App Bridge!" });
            toast.dispatch(Toast.Action.SHOW);

            // Chỉ xử lý Redirect khi click link "ngoại" (khác domain), còn link Laravel thì load trực tiếp
            var Redirect = actions.Redirect;
            document.querySelectorAll("a[data-redirect]").forEach(link => {
                link.addEventListener("click", function (e) {
                    // Nếu là link trong app của bạn thì cho phép load trực tiếp
                    if (link.href.includes(window.location.origin)) {
                        return; // không chặn
                    }

                    e.preventDefault();
                    Redirect.create(app).dispatch(Redirect.Action.APP, link.href);
                });
            });

            // Hàm fetch API có token
            window.fetchApi = async function (url, options = {}) {
                const token = await getSessionToken(app);
                return fetch(url, {
                    ...options,
                    headers: {
                        ...options.headers,
                        Authorization: `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    }
                });
            };

            // 👉 Lưu app vào window toàn cục để redirect page có thể tái sử dụng
            if (window.top) {
                try {
                    window.top.Shopify = window.top.Shopify || {};
                    window.top.Shopify.AppBridge = AppBridge;
                    window.top.Shopify.AppBridgeApp = app;
                } catch (err) {
                    console.warn("Không thể gán app vào window.top:", err);
                }
            }
        });

    </script>
</body>

</html>
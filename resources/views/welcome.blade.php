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
        <a href="{!! route('product.index', ['host' => request('host'), 'shop' => request('shop')]) !!}" data-redirect
            style="margin-right: 15px;">Products</a>
        <a href="{{ route('orders.index', ['host' => request('host'), 'shop' => request('shop')]) }}" data-redirect
            style="margin-right: 15px;">Orders</a>
        <a href="{!! route('rules.index', ['host' => request('host'), 'shop' => request('shop')]) !!}" data-redirect
            style="margin-right: 15px;">Rules</a>
    </nav>

    {{-- Nội dung trang --}}
    <div style="padding: 20px;">
        <h1>Chào mừng bạn đến với Shopify App!</h1>
        <p>Đây là trang Dashboard.</p>
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
            console.log("Host param:", host);

            var app = createApp({
                apiKey: "{{ config('shopify-app.api_key') }}",
                host: host,
                forceRedirect: false // 👈 Tạm tắt để test UI
            });

            // TitleBar
            var TitleBar = actions.TitleBar;
            TitleBar.create(app, { title: "{{ $title ?? 'Dashboard' }}" });

            // Toast test
            var Toast = actions.Toast;
            var toast = Toast.create(app, { message: "Xin chào từ Blade + App Bridge!" });
            toast.dispatch(Toast.Action.SHOW);
        });
    </script>
</body>

</html>
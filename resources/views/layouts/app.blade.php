<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'My Shopify App' }}</title>
</head>

<body>
    {{-- Navbar --}}
    <nav style="padding: 10px; background: #f4f6f8; border-bottom: 1px solid #ddd;">
        <button id="goHome" style="margin-right: 15px;">Home</button>
        <button id="goProducts" style="margin-right: 15px;">Products</button>
        <button id="goOrders">Orders</button>
        <button id="goRules">Rules</button>
    </nav>

    {{-- Nội dung trang --}}
    <div style="padding: 20px;">
        @yield('content')
    </div>

    {{-- Shopify App Bridge CDN --}}
    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var AppBridge = window['app-bridge'];
            var createApp = AppBridge.default;
            var actions = AppBridge.actions;

            var host = new URLSearchParams(window.location.search).get("host");
            console.log("Initializing App Bridge with host:", host);

            var app = createApp({
                apiKey: "{{ config('shopify-app.api_key') }}",
                host: host,
                forceRedirect: true
            });

            var TitleBar = actions.TitleBar;
            TitleBar.create(app, { title: "{{ $title ?? 'Dashboard' }}" });

            var Toast = actions.Toast;
            var toast = Toast.create(app, { message: "Xin chào từ Blade + App Bridge!" });
            toast.dispatch(Toast.Action.SHOW);

            var Redirect = actions.Redirect;

            document.getElementById("goHome").addEventListener("click", function () {
                Redirect.create(app).dispatch(Redirect.Action.APP, "/");
            });

            document.getElementById("goProducts").addEventListener("click", function () {
                console.log("Redirecting to /products");
                Redirect.create(app).dispatch(Redirect.Action.APP, "{{route('product.index')}}");
            });

            document.getElementById("goOrders").addEventListener("click", function () {
                Redirect.create(app).dispatch(Redirect.Action.APP, "/orders");
            });

            document.getElementById("goRules").addEventListener("click", function () {
                console.log("Redirecting to /rules");
                Redirect.create(app).dispatch(Redirect.Action.APP, "{{route('rules.index')}}");
            });
        });
    </script>
</body>

</html>
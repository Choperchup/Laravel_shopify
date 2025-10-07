<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Đang chuyển hướng...</title>
</head>

<body>
    <div style="padding:20px;text-align:center;">Đang chuyển hướng trong ứng dụng Shopify...</div>

    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
    <script>
        (function () {
            const redirectUrl = "{{ $redirect }}";
            const params = new URLSearchParams(window.location.search);
            const host = params.get("host") || "{{ request('host') ?? session('shopify_host') }}";

            // Đảm bảo luôn có host trong redirectUrl
            const finalUrl = redirectUrl.includes("host=")
                ? redirectUrl
                : redirectUrl + (redirectUrl.includes("?") ? "&" : "?") + "host=" + encodeURIComponent(host);

            if (window.top !== window.self) {
                // Nếu đang trong iframe (bên trong Shopify admin)
                const AppBridge = window["app-bridge"];
                const createApp = AppBridge.default;
                const { Redirect } = AppBridge.actions;

                const app = createApp({
                    apiKey: "{{ config('shopify-app.api_key') }}", // ✅ sửa ở đây
                    host: host,
                    forceRedirect: true,
                });

                Redirect.create(app).dispatch(Redirect.Action.APP, finalUrl);
            } else {
                // Nếu đang ngoài iframe (bị pop out)
                window.location.href = finalUrl;
            }
        })();
    </script>
</body>

</html>
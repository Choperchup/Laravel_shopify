<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đang chuyển hướng...</title>
    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
</head>

<body>
    <p style="text-align: center; margin-top: 100px;">
        Đang chuyển hướng trong ứng dụng Shopify...
    </p>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const apiKey = "{{ config('shopify.api_key') }}";
            const host = "{{ request('host') ?? $host ?? session('shopify_host') }}";
            const redirectUrl = `{!! $redirect !!}`;

            if (!apiKey || !host) {
                console.error("❌ Thiếu apiKey hoặc host:", { apiKey, host });
                window.location.href = redirectUrl; // fallback ngoài iframe
                return;
            }

            const app = window.ShopifyAppBridge.createApp({ apiKey, host });
            const Redirect = window.ShopifyAppBridge.actions.Redirect.create(app);

            console.log("✅ Redirecting to:", redirectUrl);
            Redirect.dispatch(
                window.ShopifyAppBridge.actions.Redirect.Action.APP,
                redirectUrl
            );
        });
    </script>
</body>

</html>
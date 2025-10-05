<!DOCTYPE html>
<html>

<head>
    <title>Redirecting...</title>
</head>

<body>
    <script>
        // Top-level redirect để thêm host parameter
        window.top.location.href = '{{ $target }}';
    </script>
    <p>Redirecting...</p>
</body>

</html>
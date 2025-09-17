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

</html>
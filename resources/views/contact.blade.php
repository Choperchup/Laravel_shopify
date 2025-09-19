@extends('shopify-app::layouts.default')

@section('content')
    <ui-title-bar title="Contact">
        <button variant="primary" onclick="location.href='{{ route('home') }}'">
            Back to Home
        </button>
    </ui-title-bar>

    <div style="padding: 20px;">
        <h1>Liên hệ</h1>
        <p>Nếu bạn cần hỗ trợ, vui lòng gửi email đến: <a href="mailto:support@yourapp.com">support@yourapp.com</a></p>
    </div>
@endsection
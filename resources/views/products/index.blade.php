@extends('shopify-app::layouts.default')

@section('content')
    <ui-title-bar title="Products">
        <button variant="primary" onclick="location.href='{{ route('home') }}'">
            Back to Home
        </button>
    </ui-title-bar>

    <div style="padding: 20px;">
        <h1>Danh sách sản phẩm</h1>

        <ul>
            @forelse($products as $product)
                <li>{{ $product['node']['title'] }}</li>
            @empty
                <li>Không có sản phẩm nào</li>
            @endforelse
        </ul>
    </div>
@endsection
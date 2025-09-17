@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
    <h1>Đăng nhập</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Đăng nhập</button>
        <a href="{{ route('password.request') }}" class="btn btn-link">Quên mật khẩu?</a>
    </form>
@endsection
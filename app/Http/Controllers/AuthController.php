<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;


class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }
    public function register(RegisterRequest $request)
    {
        $token = Str::random(64); // Tạo token trước

        $user = User::create([
            'name' => $request->name ?? 'User_' . Str::random(8),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'activation_token' => $token, // Sử dụng activation_token
            'is_active' => false,
        ]);

        Mail::to($user->email)->send(new VerifyEmail($user));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'User registered. Check email for verification.']);
        }

        return redirect()->route('login')->with('status', 'Đăng ký thành công. Kiểm tra email để xác minh.');
    }
    public function showLoginForm()
    {
        return view('auth.login');
    }
}

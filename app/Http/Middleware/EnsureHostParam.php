<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureHostParam
{
    public function handle(Request $request, Closure $next)
    {
        logger('🔎 EnsureHostParam Debug', [
            'has_host' => $request->has('host'),
            'shopify_host' => session('shopify_host'),
            'shopify_shop' => session('shopify_shop'),
            'request_shop' => $request->get('shop'),
            'referer' => $request->header('referer'),
            'method' => $request->method(),
            'ajax' => $request->ajax(),
        ]);

        // 🔒 1. Không xử lý redirect cho AJAX / JSON / PUT / DELETE
        if (
            !$request->isMethod('GET') ||
            $request->ajax() ||
            $request->wantsJson()
        ) {
            return $next($request);
        }

        // 🔒 2. Nếu đã có host & shop => chỉ cần lưu session và cho qua
        if ($request->has('host')) {
            session(['shopify_host' => $request->get('host')]);
        }
        if ($request->has('shop')) {
            session(['shopify_shop' => $request->get('shop')]);
        }
        if ($request->has('host') && $request->has('shop')) {
            return $next($request);
        }

        // 🔒 3. Nếu chưa có, mà session có thì redirect bổ sung host/shop
        if (session()->has('shopify_host') && session()->has('shopify_shop')) {
            $host = session('shopify_host');
            $shop = session('shopify_shop');

            // Nếu URL hiện tại đã có host rồi → tránh lặp
            if (str_contains($request->fullUrl(), 'host=' . $host)) {
                return $next($request);
            }

            $target = $request->fullUrlWithQuery([
                'host' => $host,
                'shop' => $shop,
            ]);

            logger('🔎 Redirecting to', [
                'target' => $target,
                'host' => $host,
                'shop' => $shop,
                'referer' => $request->header('referer'),
            ]);

            // Dùng redirect bình thường thay vì view JS để tránh sai method
            return redirect()->to($target);
        }

        // 🔒 4. Nếu không có gì hết, cứ cho qua
        return $next($request);
    }
}

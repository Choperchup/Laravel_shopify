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
        ]);
        // nếu thiếu host & đã lưu trong session -> trả về 1 view JS để top-level redirect
        if (!$request->has('host') && session()->has('shopify_host')) {
            $host = session('shopify_host');
            $shop = session('shopify_shop') ?? $request->get('shop');
            $target = $request->fullUrlWithQuery(['host' => $host, 'shop' => session('shopify_shop') ?? $request->get('shop')]);

            logger('🔎 Redirecting to', ['target' => $target, 'host' => $host, 'shop' => $shop, 'referer' => $request->header('referer')]);

            return response()->view('shopify.append-host', [
                'target' => $target,
            ], 200);
        }

        if ($request->has('host')) {
            session(['shopify_host' => $request->get('host')]);
        }
        if ($request->has('shop')) {
            session(['shopify_shop' => $request->get('shop')]);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureHostParam
{
    public function handle(Request $request, Closure $next)
    {
        // nếu thiếu host & đã lưu trong session -> trả về 1 view JS để top-level redirect
        if (!$request->has('host') && session()->has('shopify_host')) {
            $host = session('shopify_host');
            $target = $request->fullUrlWithQuery(['host' => $host, 'shop' => session('shopify_shop') ?? $request->get('shop')]);

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

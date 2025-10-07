<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureShopifyHmac
{
    public function handle(Request $request, Closure $next)
    {
        // chỉ kiểm tra HMAC cho callback và auth routes
        if (! $request->is('auth/*') && ! $request->is('billing/*')) {
            return $next($request);
        }

        $hmac = $request->query('hmac');
        $queryString = $request->server->get('QUERY_STRING');

        $parts = [];
        foreach (explode('&', $queryString) as $part) {
            if (
                str_starts_with($part, 'hmac=')
                || str_starts_with($part, 'id_token=')
                || str_starts_with($part, 'session=')
            ) {
                continue;
            }
            $parts[] = $part;
        }

        sort($parts, SORT_STRING);
        $computedString = implode('&', $parts);
        $calculated = hash_hmac('sha256', $computedString, env('SHOPIFY_API_SECRET'));

        logger('🔎 Shopify HMAC Debug (RAW)', [
            'computed_string' => $computedString,
            'provided_hmac'   => $hmac,
            'calculated_hmac' => $calculated,
            'api_secret' => env('SHOPIFY_API_SECRET'), // Thêm để kiểm tra secret
        ]);

        if (!$hmac || !hash_equals($calculated, $hmac)) {
            return response('HMAC verification failed', 403);
        }

        return $next($request);
    }
}

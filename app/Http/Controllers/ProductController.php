<?php

namespace App\Http\Controllers;

use App\Services\ShopifyGraphQLService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $shopifyService;

    public function __construct(ShopifyGraphQLService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    /**
     * Hiển thị trang chính với sản phẩm
     */
    public function index(Request $request)
    {
        // Lấy shop hiện tại từ session hoặc request (từ Shopify)
        $currentShopDomain = $this->getCurrentShopDomain($request);

        // Lấy shop hiện tại trong DB
        $selectedShop = $this->shopifyService->getShopByDomain($currentShopDomain);
        if (!$selectedShop) {
            return view('welcome', [
                'shops' => collect(),
                'selectedShop' => null,
                'products' => [],
                'pageInfo' => null,
                'perPage' => 50,
                'error' => 'Shop hiện tại không tồn tại trong hệ thống.'
            ]);
        }

        // Không cho phép đổi shop qua query; luôn dùng shop hiện tại

        // Pagination parameters
        $perPage = (int) $request->get('per_page', 50);
        $after = $request->get('after');

        // Validate per_page values
        if (!in_array($perPage, [50, 100, 250])) {
            $perPage = 50;
        }

        $products = [];
        $pageInfo = null;
        $error = null;

        if ($selectedShop) {
            $result = $this->shopifyService->getProducts($selectedShop, $perPage, $after);
            $products = $result['products'];
            $pageInfo = $result['pageInfo'];
            $error = $result['error'];
        } else {
            $error = 'Shop không tồn tại hoặc đã bị xóa';
        }

        return view('welcome', [
            'shops' => collect(),
            'selectedShop' => $selectedShop,
            'products' => $products,
            'pageInfo' => $pageInfo,
            'perPage' => $perPage,
            'error' => $error,
        ]);
    }

    /**
     * API endpoint để lấy sản phẩm via AJAX
     */
    public function getProducts(Request $request)
    {
        $request->validate([
            'per_page' => 'in:50,100,250',
            'after' => 'nullable|string'
        ]);

        // Bảo mật: Chỉ cho phép truy cập shop hiện tại
        $currentShopDomain = $this->getCurrentShopDomain($request);
        $shop = $this->shopifyService->getShopByDomain($currentShopDomain);

        if (!$shop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop hiện tại không tồn tại'
            ], 404);
        }

        $perPage = (int) $request->get('per_page', 50);
        $after = $request->get('after');

        $result = $this->shopifyService->getProducts($shop, $perPage, $after);

        return response()->json([
            'success' => $result['error'] === null,
            'products' => $result['products'],
            'pageInfo' => $result['pageInfo'],
            'error' => $result['error']
        ]);
    }

    /**
     * API endpoint để lấy danh sách shops
     */
    public function getShops(Request $request)
    {
        // Bảo mật: Chỉ trả về shop hiện tại
        $currentShopDomain = $this->getCurrentShopDomain($request);
        $currentShop = $this->shopifyService->getShopByDomain($currentShopDomain);

        if (!$currentShop) {
            return response()->json([
                'success' => false,
                'message' => 'Shop hiện tại không tồn tại'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'shops' => [
                [
                    'domain' => $currentShop->name,
                    'name' => $currentShop->email ?: $currentShop->name,
                    'shop_name' => $currentShop->name
                ]
            ]
        ]);
    }

    /**
     * Lấy domain của shop hiện tại từ request hoặc session
     */
    private function getCurrentShopDomain(Request $request): string
    {
        // Thử lấy từ Shopify session (Shopify package thường lưu ở đây)
        if (session()->has('shopify_domain')) {
            return session('shopify_domain');
        }

        // Thử lấy từ session với key khác (tùy thuộc vào Shopify package)
        if (session()->has('shop')) {
            return session('shop');
        }

        // Thử lấy từ request headers (Shopify app thường gửi qua header)
        $shopifyShop = $request->header('X-Shopify-Shop-Domain');
        if ($shopifyShop) {
            return $shopifyShop;
        }

        // Thử lấy từ query parameter (khi truy cập trực tiếp)
        $shopFromQuery = $request->get('shop');
        if ($shopFromQuery) {
            return $shopFromQuery;
        }

        // Thử lấy từ URL referer (nếu có)
        $referer = $request->header('Referer');
        if ($referer && preg_match('/admin\.shopify\.com\/store\/([^\/]+)/', $referer, $matches)) {
            return $matches[1] . '.myshopify.com';
        }

        // Fallback: lấy shop đầu tiên trong database
        $shops = $this->shopifyService->getAllShops();
        return $shops->isNotEmpty() ? $shops->first()->name : '';
    }
}

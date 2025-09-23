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
        $currentShopDomain = $this->getCurrentShopDomain($request);
        $selectedShop = $this->shopifyService->getShopByDomain($currentShopDomain);

        if (!$selectedShop) {
            return view('welcome', [
                'shops' => collect(),
                'selectedShop' => null,
                'products' => [],
                'pageInfo' => null,
                'perPage' => 50,
                'error' => 'Shop hiện tại không tồn tại trong hệ thống.',
                'filters' => [],
                'sort' => ['field' => 'title', 'direction' => 'ASC'],
                'collections' => [],
                'vendors' => [],
                'productTypes' => [],
                'tags' => []
            ]);
        }

        $perPage = (int) $request->get('per_page', 50);
        $after = $request->get('after');

        if (!in_array($perPage, [50, 100, 250])) {
            $perPage = 50;
        }

        $filters = [
            'title' => $request->get('title', ''),
            'status' => $request->get('status', ''),
            'tag' => $request->get('tag', ''),
            'collection' => $request->get('collection', ''),
            'vendors' => array_filter(explode(',', $request->get('vendors', ''))),
            'productTypes' => array_filter(explode(',', $request->get('productTypes', '')))
        ];

        $sort = [
            'field' => $request->get('sortField', 'title'),
            'direction' => $request->get('sortDirection', 'ASC')
        ];

        $products = [];
        $pageInfo = null;
        $error = null;
        $collections = [];
        $vendors = [];
        $productTypes = [];
        $tags = [];

        if ($selectedShop) {
            $result = $this->shopifyService->getProducts($selectedShop, $perPage, $after, $filters, $sort);
            $products = $result['products'];
            $pageInfo = $result['pageInfo'];
            $error = $result['error'];

            $collections = $this->shopifyService->getCollections($selectedShop);
            $filtersData = $this->shopifyService->getProductFilters($selectedShop);
            $vendors = $filtersData['vendors'];
            $productTypes = $filtersData['productTypes'];
            $tags = $filtersData['tags'];
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
            'filters' => $filters,
            'sort' => $sort,
            'collections' => $collections,
            'vendors' => $vendors,
            'productTypes' => $productTypes,
            'tags' => $tags
        ]);
    }

    /**
     * API endpoint để lấy sản phẩm via AJAX
     */
    public function getProducts(Request $request)
    {
        $request->validate([
            'per_page' => 'in:50,100,250',
            'after' => 'nullable|string',
            'title' => 'nullable|string',
            'status' => 'nullable|in:ACTIVE,DRAFT,ARCHIVED',
            'tag' => 'nullable|string',
            'collection' => 'nullable|string',
            'vendors' => 'nullable|string',
            'productTypes' => 'nullable|string',
            'sortField' => 'nullable|in:title,createdAt,updatedAt,productType,vendor',
            'sortDirection' => 'nullable|in:ASC,DESC'
        ]);

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

        $filters = [
            'title' => $request->get('title', ''),
            'status' => $request->get('status', ''),
            'tag' => $request->get('tag', ''),
            'collection' => $request->get('collection', ''),
            'vendors' => array_filter(explode(',', $request->get('vendors', ''))),
            'productTypes' => array_filter(explode(',', $request->get('productTypes', '')))
        ];

        $sort = [
            'field' => $request->get('sortField', 'title'),
            'direction' => $request->get('sortDirection', 'ASC')
        ];

        $result = $this->shopifyService->getProducts($shop, $perPage, $after, $filters, $sort);

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
        if (session()->has('shopify_domain')) {
            return session('shopify_domain');
        }

        if (session()->has('shop')) {
            return session('shop');
        }

        $shopifyShop = $request->header('X-Shopify-Shop-Domain');
        if ($shopifyShop) {
            return $shopifyShop;
        }

        $shopFromQuery = $request->get('shop');
        if ($shopFromQuery) {
            return $shopFromQuery;
        }

        $referer = $request->header('Referer');
        if ($referer && preg_match('/admin\.shopify\.com\/store\/([^\/]+)/', $referer, $matches)) {
            return $matches[1] . '.myshopify.com';
        }

        $shops = $this->shopifyService->getAllShops();
        return $shops->isNotEmpty() ? $shops->first()->name : '';
    }
}

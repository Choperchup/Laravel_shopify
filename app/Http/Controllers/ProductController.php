<?php

namespace App\Http\Controllers;

use App\Jobs\BulkUpdateProductStatusJob;
use App\Jobs\BulkAddTagsJob;
use App\Jobs\BulkRemoveTagsJob;
use App\Jobs\BulkAddToCollectionJob;
use App\Jobs\BulkRemoveFromCollectionJob;
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

        if (!in_array($perPage, [50, 100, 250])) {
            $perPage = 50;
        }

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
     * Bulk Actions API
     */
    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:active,draft,archive,add_tags,remove_tags,add_collection,remove_collection',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'string|regex:/^gid:\/\/shopify\/Product\/[0-9]+$/',
            'tags' => 'required_if:action,add_tags,remove_tags|array|min:1',
            'tags.*' => 'string|max:255',
            'collection_id' => 'required_if:action,add_collection,remove_collection|string|regex:/^gid:\/\/shopify\/Collection\/[0-9]+$/'
        ]);

        $currentShopDomain = $this->getCurrentShopDomain($request);
        $shop = $this->shopifyService->getShopByDomain($currentShopDomain);

        if (!$shop) {
            Log::error('Bulk Action Failed: Shop not found', ['domain' => $currentShopDomain]);
            return response()->json(['success' => false, 'message' => 'Shop không tồn tại'], 404);
        }

        $productIds = $request->input('product_ids');
        $action = $request->input('action');

        Log::info('Dispatching Bulk Action', [
            'action' => $action,
            'shop' => $shop->name,
            'product_count' => count($productIds)
        ]);

        try {
            switch ($action) {
                case 'active':
                    $status = 'ACTIVE';
                    BulkUpdateProductStatusJob::dispatch($shop, $productIds, $status);
                    break;
                case 'draft':
                    $status = 'DRAFT';
                    BulkUpdateProductStatusJob::dispatch($shop, $productIds, $status);
                    break;
                case 'archive':
                    $status = 'ARCHIVED';
                    BulkUpdateProductStatusJob::dispatch($shop, $productIds, $status);
                    break;
                case 'add_tags':
                    $tags = array_filter($request->input('tags', []));
                    if (empty($tags)) {
                        return response()->json(['success' => false, 'message' => 'Tags không được rỗng'], 400);
                    }
                    BulkAddTagsJob::dispatch($shop, $productIds, $tags);
                    break;
                case 'remove_tags':
                    $tags = array_filter($request->input('tags', []));
                    if (empty($tags)) {
                        return response()->json(['success' => false, 'message' => 'Tags không được rỗng'], 400);
                    }
                    BulkRemoveTagsJob::dispatch($shop, $productIds, $tags);
                    break;
                case 'add_collection':
                    BulkAddToCollectionJob::dispatch($shop, $request->input('collection_id'), $productIds);
                    break;
                case 'remove_collection':
                    BulkRemoveFromCollectionJob::dispatch($shop, $request->input('collection_id'), $productIds);
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Action không hợp lệ'], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk action đã được queued. Kiểm tra queue để theo dõi.'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk Action Dispatch Failed', [
                'action' => $action,
                'shop' => $shop->name,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

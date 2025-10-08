<?php

namespace App\Http\Controllers;

use App\Jobs\BulkProductActionJob;
use App\Models\User;
use App\Services\ProductGraphQLService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ProductGraphQLController extends Controller
{
    protected ProductGraphQLService $productService;

    public function __construct(ProductGraphQLService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Lấy shop domain từ session/header/query
     */
    private function getCurrentShopDomain(Request $request): string
    {
        if (session()->has('shopify_domain')) {
            return session('shopify_domain');
        }

        if (session()->has('shop')) {
            return session('shop');
        }

        if ($shop = $request->header('X-Shopify-Shop-Domain')) {
            return $shop;
        }

        if ($shop = $request->get('shop')) {
            return $shop;
        }

        if ($referer = $request->header('Referer')) {
            if (preg_match('/admin\.shopify\.com\/store\/([^\/]+)/', $referer, $m)) {
                return $m[1] . '.myshopify.com';
            }
        }

        return '';
    }

    public function index(Request $request)
    {
        $currentShopDomain = $this->getCurrentShopDomain($request);
        Log::info('product.index accessed', [
            'currentShopDomain' => $currentShopDomain,
            'request' => $request->all(),
            'session_keys' => array_keys(session()->all()),
            'headers' => $request->header(),
            'isAjax' => $request->ajax(),
        ]);

        $defaultPage = 50; // 📌 default 50 theo yêu cầu

        if (empty($currentShopDomain)) {
            Log::warning('No shop domain found', [
                'session_keys' => array_keys(session()->all()),
                'referer'      => $request->header('Referer'),
            ]);
            $firstShop = $this->productService->getFirstShop();
            $currentShopDomain = $firstShop?->name ?? '';
        }

        $selectedShop = $this->productService->getShopByDomain($currentShopDomain);
        if (!$selectedShop) {
            Log::error('Shop not found', ['domain' => $currentShopDomain]);
            return view('products.index', [
                'products'  => [],
                'pageInfo'  => null,
                'perPage'   => $defaultPage,
                'error'     => "Shop '{$currentShopDomain}' không tồn tại trong hệ thống.",
            ]);
        }

        // ===== Pagination & Filters =====
        $perPage = (int) $request->get('per_page', $defaultPage);
        if (!in_array($perPage, [50, 100, 250])) {
            $perPage = $defaultPage;
        }

        $after  = $request->get('after');
        $before = $request->get('before');
        $sort   = $request->get('sort', 'title');
        $order  = $request->get('order', 'asc');
        $collectionId = $request->get('collection');

        $products = [];
        $pageInfo = null;

        // ===== Nếu có collection → bỏ qua mọi filter khác =====
        if ($collectionId) {
            $result = $this->productService->getProductsByCollection(
                $selectedShop,
                $collectionId,
                $perPage,
                $after,
                $before,
                $sort,
                $order
            );
        } else {
            // ===== Xây filters khi KHÔNG chọn collection =====
            $filters   = [];
            $title     = trim((string) $request->get('title', ''));
            $status    = (array) $request->get('status', []);
            $tag       = trim((string) $request->get('tag', ''));
            $vendors   = (array) $request->get('vendors', []);
            $types     = (array) $request->get('types', []);

            if ($title !== '') {
                $filters[] = 'title:*' . addcslashes($title, '"') . '*';
            }
            if (!empty($status)) {
                $statusFilters = array_map(fn($s) => 'status:' . strtoupper($s), $status);
                $filters[] = '(' . implode(' OR ', $statusFilters) . ')';
            }
            if ($tag !== '') {
                $filters[] = 'tag:"' . addcslashes($tag, '"') . '"';
            }
            if (!empty($vendors)) {
                $filters[] = '(' . implode(
                    ' OR ',
                    array_map(fn($v) => 'vendor:"' . addcslashes($v, '"') . '"', $vendors)
                ) . ')';
            }
            if (!empty($types)) {
                $filters[] = '(' . implode(
                    ' OR ',
                    array_map(fn($t) => 'product_type:"' . addcslashes($t, '"') . '"', $types)
                ) . ')';
            }

            $searchQuery = count($filters) ? implode(' ', $filters) : null;

            $result = $this->productService->getProducts(
                $selectedShop,
                $perPage,
                $after,
                $before,
                $searchQuery,
                $sort,
                $order
            );
        }

        $products = $result['products'] ?? [];
        $pageInfo = $result['pageInfo'] ?? null;

        // ===== Data cho dropdown filter =====
        $productTags        = $this->productService->getTags($selectedShop);
        $productVendors     = $this->productService->getVendors($selectedShop);
        $productTypes       = $this->productService->getProductType($selectedShop);
        $productCollections = $this->productService->getCollections($selectedShop);

        // ===== AJAX (partial reload) =====
        if ($request->ajax()) {
            return response()->json([
                'table'      => view('products.partials.table_body', compact('products'))->render(),
                'pagination' => view('products.partials.pagination', compact('pageInfo'))->render(),
            ]);
        }

        // ===== Full page load =====
        return view('products.index', [
            'products'     => $products,
            'pageInfo'     => $pageInfo,
            'tags'         => $productTags,
            'vendors'      => $productVendors,
            'productTypes' => $productTypes,
            'collections'  => $productCollections,
            'perPage'      => $perPage,
            'sort'         => $sort,
            'order'        => $order,
        ]);
    }
    public function bulkAction(Request $request)
    {
        $currentShopDomain = $this->getCurrentShopDomain($request);
        $shop = User::where('name', $currentShopDomain)->first();

        if (!$shop) {
            return response()->json(['error' => 'Shop not found'], 404);
        }

        // ✅ Hỗ trợ cả 2 kiểu key: product_ids hoặc productIds
        $productIds = $request->input('product_ids') ?? $request->input('productIds', []);
        $action     = $request->input('action');
        $payload    = $request->input('payload', []);

        if (empty($productIds)) {
            Log::warning('⚠️ Bulk action: no product IDs received', [
                'action' => $action,
                'payload' => $payload,
                'raw' => $request->all(),
            ]);
            return response()->json(['error' => 'No products selected'], 422);
        }

        // 🔧 Fix collection ID type
        if (in_array($action, ['add_collection', 'remove_collection']) && isset($payload['collection_id'])) {
            if (is_array($payload['collection_id'])) {
                $payload['collection_id'] = $payload['collection_id'][0] ?? null;
            }
        }

        Log::info("🚀 Dispatching BulkProductActionJob", [
            'shop' => $shop->name,
            'action' => $action,
            'productIds' => $productIds,
            'payload' => $payload,
        ]);

        // ✅ Tạo batch job
        $jobs = [];
        foreach ($productIds as $id) {
            $jobs[] = new BulkProductActionJob($shop, $action, [$id], $payload);
        }

        $batch = Bus::batch($jobs)->dispatch();

        return response()->json([
            'batch_id' => $batch->id,
            'message'  => 'Bulk action queued successfully',
        ]);
    }
}

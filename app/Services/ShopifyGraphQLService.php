<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyGraphQLService
{
    /**
     * Lấy sản phẩm từ Shopify GraphQL API
     * 
     * @param User $shop
     * @param int $first - Số lượng sản phẩm (pagination)
     * @param string|null $after - Cursor cho pagination
     * @param array $filters - Bộ lọc (title, status, tag, vendors, productTypes, collection)
     * @param array $sort - Sắp xếp (field, direction)
     * @return array
     */
    public function getProducts(User $shop, int $first = 50, ?string $after = null, array $filters = [], array $sort = []): array
    {
        $query = $this->buildProductsQuery($first, $after, $filters, $sort);

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("https://{$shop->name}/admin/api/2024-04/graphql.json", [
                'query' => $query
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['errors'])) {
                    Log::error('Shopify GraphQL Errors', [
                        'shop' => $shop->name,
                        'errors' => $data['errors']
                    ]);
                    return ['products' => [], 'pageInfo' => null, 'error' => $data['errors']];
                }

                return $this->formatProductsResponse($data);
            }

            Log::error('Shopify API Request Failed', [
                'shop' => $shop->name,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return ['products' => [], 'pageInfo' => null, 'error' => 'API request failed'];
        } catch (\Exception $e) {
            Log::error('Shopify GraphQL Service Error', [
                'shop' => $shop->name,
                'error' => $e->getMessage()
            ]);

            return ['products' => [], 'pageInfo' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Tạo GraphQL query để lấy sản phẩm
     */
    private function buildProductsQuery(int $first, ?string $after = null, array $filters = [], array $sort = []): string
    {
        $afterClause = $after ? ', after: "' . $after . '"' : '';
        $queryFilter = '';
        $sortKey = strtoupper($sort['field'] ?? 'TITLE');
        $sortDirection = isset($sort['direction']) && $sort['direction'] === 'DESC' ? 'reverse: true' : '';

        // Map sort field to Shopify GraphQL sort keys
        $sortKeyMap = [
            'title' => 'TITLE',
            'createdAt' => 'CREATED_AT',
            'updatedAt' => 'UPDATED_AT',
            'productType' => 'PRODUCT_TYPE',
            'vendor' => 'VENDOR'
        ];
        $sortKey = $sortKeyMap[$sort['field'] ?? 'title'] ?? 'TITLE';

        // Build filter query
        if (!empty($filters['collection'])) {
            // Collection filter cannot be combined with other filters
            $queryFilter = 'query: "from:' . addslashes($filters['collection']) . '"';
        } else {
            $conditions = [];
            if (!empty($filters['title'])) {
                $conditions[] = addslashes($filters['title']);
            }
            if (!empty($filters['status'])) {
                $conditions[] = 'status:' . $filters['status'];
            }
            if (!empty($filters['tag'])) {
                $conditions[] = 'tag:' . addslashes($filters['tag']);
            }
            if (!empty($filters['vendors'])) {
                $vendorConditions = [];
                foreach ($filters['vendors'] as $vendor) {
                    $vendorConditions[] = 'vendor:' . addslashes($vendor);
                }
                $conditions[] = '(' . implode(' OR ', $vendorConditions) . ')';
            }
            if (!empty($filters['productTypes'])) {
                $types = array_map('addslashes', $filters['productTypes']);
                $conditions[] = 'product_type:(' . implode(' OR ', $types) . ')';
            }
            if (!empty($conditions)) {
                $queryFilter = 'query: "' . implode(' ', $conditions) . '"';
            }
        }

        return '
        query getProducts {
            products(first: ' . $first . $afterClause . ($queryFilter ? ', ' . $queryFilter : '') . ', sortKey: ' . $sortKey . ($sortDirection ? ', ' . $sortDirection : '') . ') {
                edges {
                    node {
                        id
                        title
                        handle
                        description
                        descriptionHtml
                        status
                        vendor
                        productType
                        tags
                        createdAt
                        updatedAt
                        images(first: 5) {
                            edges {
                                node {
                                    id
                                    url
                                    altText
                                    width
                                    height
                                }
                            }
                        }
                        variants(first: 10) {
                            edges {
                                node {
                                    id
                                    title
                                    sku
                                    inventoryQuantity
                                    availableForSale
                                }
                            }
                        }
                        priceRange {
                            minVariantPrice {
                                amount
                                currencyCode
                            }
                            maxVariantPrice {
                                amount
                                currencyCode
                            }
                        }
                    }
                    cursor
                }
                pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                }
            }
        }';
    }

    /**
     * Format response từ Shopify API
     */
    private function formatProductsResponse(array $data): array
    {
        $products = [];
        $pageInfo = null;

        if (isset($data['data']['products'])) {
            $productsData = $data['data']['products'];

            foreach ($productsData['edges'] as $edge) {
                $product = $edge['node'];

                $images = [];
                if (isset($product['images']['edges'])) {
                    foreach ($product['images']['edges'] as $imageEdge) {
                        $images[] = $imageEdge['node'];
                    }
                }

                $variants = [];
                if (isset($product['variants']['edges'])) {
                    foreach ($product['variants']['edges'] as $variantEdge) {
                        $variants[] = $variantEdge['node'];
                    }
                }

                $products[] = [
                    'id' => $product['id'],
                    'title' => $product['title'],
                    'handle' => $product['handle'],
                    'description' => $product['description'],
                    'descriptionHtml' => $product['descriptionHtml'],
                    'status' => $product['status'],
                    'vendor' => $product['vendor'],
                    'productType' => $product['productType'],
                    'tags' => $product['tags'],
                    'createdAt' => $product['createdAt'],
                    'updatedAt' => $product['updatedAt'],
                    'images' => $images,
                    'variants' => $variants,
                    'priceRange' => $product['priceRange'],
                    'cursor' => $edge['cursor']
                ];
            }

            $pageInfo = $productsData['pageInfo'];
        }

        return [
            'products' => $products,
            'pageInfo' => $pageInfo,
            'error' => null
        ];
    }

    /**
     * Lấy danh sách bộ sưu tập
     */
    public function getCollections(User $shop): array
    {
        $query = '
        query getCollections {
            collections(first: 100) {
                edges {
                    node {
                        id
                        title
                    }
                }
            }
        }';

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("https://{$shop->name}/admin/api/2024-04/graphql.json", [
                'query' => $query
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['errors'])) {
                    Log::error('Shopify GraphQL Collections Errors', [
                        'shop' => $shop->name,
                        'errors' => $data['errors']
                    ]);
                    return [];
                }

                $collections = [];
                if (isset($data['data']['collections']['edges'])) {
                    foreach ($data['data']['collections']['edges'] as $edge) {
                        $collections[] = $edge['node'];
                    }
                }
                return $collections;
            }

            Log::error('Shopify API Collections Request Failed', [
                'shop' => $shop->name,
                'status' => $response->status()
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Shopify GraphQL Collections Error', [
                'shop' => $shop->name,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Lấy danh sách tags, vendors, product types
     */
    public function getProductFilters(User $shop): array
    {
        $query = '
        query getProductFilters {
            products(first: 250) {
                edges {
                    node {
                        vendor
                        productType
                        tags
                    }
                }
            }
        }';

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("https://{$shop->name}/admin/api/2024-04/graphql.json", [
                'query' => $query
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['errors'])) {
                    Log::error('Shopify GraphQL Filters Errors', [
                        'shop' => $shop->name,
                        'errors' => $data['errors']
                    ]);
                    return ['vendors' => [], 'productTypes' => [], 'tags' => []];
                }

                $vendors = [];
                $productTypes = [];
                $tags = [];

                if (isset($data['data']['products']['edges'])) {
                    foreach ($data['data']['products']['edges'] as $edge) {
                        $product = $edge['node'];
                        if ($product['vendor']) {
                            $vendors[] = $product['vendor'];
                        }
                        if ($product['productType']) {
                            $productTypes[] = $product['productType'];
                        }
                        if (!empty($product['tags'])) {
                            $tags = array_merge($tags, $product['tags']);
                        }
                    }
                }

                return [
                    'vendors' => array_values(array_unique(array_filter($vendors))),
                    'productTypes' => array_values(array_unique(array_filter($productTypes))),
                    'tags' => array_values(array_unique(array_filter($tags)))
                ];
            }

            Log::error('Shopify API Filters Request Failed', [
                'shop' => $shop->name,
                'status' => $response->status()
            ]);
            return ['vendors' => [], 'productTypes' => [], 'tags' => []];
        } catch (\Exception $e) {
            Log::error('Shopify GraphQL Filters Error', [
                'shop' => $shop->name,
                'error' => $e->getMessage()
            ]);
            return ['vendors' => [], 'productTypes' => [], 'tags' => []];
        }
    }

    /**
     * Lấy GraphQL URL cho shop
     */
    private function getGraphqlUrl(User $shop): string
    {
        return "https://{$shop->name}/admin/api/" . config('shopify-app.api_version', '2024-04') . "/graphql.json";
    }

    /**
     * Lấy tất cả shops có sẵn
     */
    public function getAllShops(): \Illuminate\Database\Eloquent\Collection
    {
        return User::all();
    }

    /**
     * Lấy shop theo domain
     */
    public function getShopByDomain(string $domain): ?User
    {
        return User::where('name', $domain)->first();
    }
}

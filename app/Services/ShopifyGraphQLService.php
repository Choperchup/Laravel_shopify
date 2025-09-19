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
     * @return array
     */
    public function getProducts(User $shop, int $first = 50, ?string $after = null): array
    {
        $query = $this->buildProductsQuery($first, $after);

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password, // Shopify package lưu access token ở field password
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("https://{$shop->name}/admin/api/2024-04/graphql.json", [
                'query' => $query
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['errors'])) {
                    Log::error('Shopify GraphQL Errors', [
                        'shop' => $shop->name, // Shopify package lưu domain ở field name
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
    private function buildProductsQuery(int $first, ?string $after = null): string
    {
        $afterClause = $after ? ', after: "' . $after . '"' : '';

        return '
        query getProducts {
            products(first: ' . $first . $afterClause . ') {
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

            // Format products
            foreach ($productsData['edges'] as $edge) {
                $product = $edge['node'];

                // Format images
                $images = [];
                if (isset($product['images']['edges'])) {
                    foreach ($product['images']['edges'] as $imageEdge) {
                        $images[] = $imageEdge['node'];
                    }
                }

                // Format variants  
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

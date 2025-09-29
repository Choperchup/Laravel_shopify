<?php

namespace App\Services;

use App\Models\User;
use App\Models\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyGraphQLService
{
    private function getGraphqlUrl(User $shop): string
    {
        return "https://{$shop->name}/admin/api/2024-04/graphql.json";
    }

    public function getProducts(User $shop, int $first = 50, ?string $after = null, array $filters = [], array $sort = []): array
    {
        $query = $this->buildProductsQuery($first, $after, $filters, $sort);

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->getGraphqlUrl($shop), [
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

    private function buildProductsQuery(int $first, ?string $after = null, array $filters = [], array $sort = []): string
    {
        $afterClause = $after ? ', after: "' . addslashes($after) . '"' : '';
        $queryFilter = '';
        $sortClause = '';

        $sortKeyMap = [
            'title' => 'TITLE',
            'createdAt' => 'CREATED_AT',
            'updatedAt' => 'UPDATED_AT',
            'productType' => 'PRODUCT_TYPE',
            'vendor' => 'VENDOR'
        ];
        $sortKey = strtoupper($sort['field'] ?? 'TITLE');
        $sortKey = $sortKeyMap[$sort['field'] ?? 'title'] ?? 'TITLE';
        $sortDirection = isset($sort['direction']) && strtoupper($sort['direction']) === 'DESC' ? 'REVERSE' : '';

        if ($sortKey && $sortDirection) {
            $sortClause = ', sortKey: ' . $sortKey . ', reverse: ' . $sortDirection;
        } elseif ($sortKey) {
            $sortClause = ', sortKey: ' . $sortKey;
        }

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
            $queryFilter = ', query: "' . implode(' ', $conditions) . '"';
        }

        return '
    query getProducts {
        products(first: ' . $first . $afterClause . $queryFilter . $sortClause . ') {
            edges {
                node {
                    id
                    title
                    handle
                    status
                    vendor
                    productType
                    tags
                    createdAt
                    updatedAt
                    collections(first: 10) {
                        edges {
                            node {
                                id
                                title
                            }
                        }
                    }
                    images(first: 1) {
                        edges {
                            node {
                                originalSrc
                            }
                        }
                    }
                    variants(first: 250) {
                        edges {
                            node {
                                id
                                title
                                price
                                compareAtPrice
                                inventoryQuantity
                                sku
                            }
                        }
                    }
                }
            }
            pageInfo {
                hasNextPage
                endCursor
                hasPreviousPage
                startCursor
            }
        }
    }';
    }

    private function formatProductsResponse(array $data): array
    {
        $products = [];
        $collectionIdFilter = request()->get('collection'); // Lấy collection_id từ request

        foreach ($data['data']['products']['edges'] ?? [] as $edge) {
            $node = $edge['node'];
            $productCollections = array_map(fn($e) => $e['node'], $node['collections']['edges'] ?? []);

            // Lọc sản phẩm theo collection_id nếu có
            if ($collectionIdFilter && !in_array($collectionIdFilter, array_column($productCollections, 'id'))) {
                continue; // Bỏ qua sản phẩm không thuộc collection được chọn
            }

            $products[] = [
                'id' => $node['id'],
                'title' => $node['title'],
                'handle' => $node['handle'],
                'status' => $node['status'],
                'vendor' => $node['vendor'],
                'productType' => $node['productType'],
                'tags' => $node['tags'],
                'createdAt' => $node['createdAt'],
                'updatedAt' => $node['updatedAt'],
                'collections' => $productCollections,
                'image' => $node['images']['edges'][0]['node']['originalSrc'] ?? null,
                'variants' => array_map(fn($e) => $e['node'], $node['variants']['edges'] ?? []),
            ];
        }

        return [
            'products' => $products,
            'pageInfo' => $data['data']['products']['pageInfo'] ?? null,
            'error' => null
        ];
    }

    public function getCollections(User $shop): array
    {
        $query = '
        query {
            collections(first: 250) {
                edges {
                    node {
                        id
                        title
                    }
                }
            }
        }';

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->password,
            'Content-Type' => 'application/json'
        ])->post($this->getGraphqlUrl($shop), ['query' => $query]);

        if ($response->successful()) {
            $data = $response->json();
            return array_map(fn($e) => $e['node'], $data['data']['collections']['edges'] ?? []);
        }

        return [];
    }

    public function getProductFilters(User $shop): array
    {
        $products = $this->getProducts($shop, 250)['products'] ?? [];
        $vendors = array_unique(array_filter(array_column($products, 'vendor')));
        $productTypes = array_unique(array_filter(array_column($products, 'productType')));
        $tags = array_unique(array_merge(...array_column($products, 'tags')));

        sort($vendors);
        sort($productTypes);
        sort($tags);

        return ['vendors' => $vendors, 'productTypes' => $productTypes, 'tags' => $tags];
    }

    public function bulkUpdateProductStatus(User $shop, array $productIds, string $status): array
    {
        $results = [];
        $batches = array_chunk($productIds, 50);

        foreach ($batches as $batch) {
            $mutations = [];
            foreach ($batch as $index => $id) {
                $alias = 'update' . $index;
                $mutations[] = $alias . ': productUpdate(input: {id: "' . $id . '", status: ' . $status . '}) { product { id } userErrors { field message } }';
            }

            $query = 'mutation { ' . implode(' ', $mutations) . ' }';

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json'
            ])->post($this->getGraphqlUrl($shop), ['query' => $query]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['errors'])) {
                    $results[] = ['batch' => $batch, 'success' => false, 'error' => $data['errors']];
                    continue;
                }
                $hasError = false;
                foreach ($data['data'] as $res) {
                    if (!empty($res['userErrors'])) {
                        $hasError = true;
                        break;
                    }
                }
                $results[] = ['batch' => $batch, 'success' => !$hasError, 'data' => $data['data']];
            } else {
                $results[] = ['batch' => $batch, 'success' => false, 'error' => 'API request failed'];
            }

            usleep(1000000); // 1 giây throttle
        }

        return ['success' => !in_array(false, array_column($results, 'success')), 'results' => $results];
    }

    public function bulkAddTags(User $shop, array $productIds, array $tags): array
    {
        $results = [];
        $batches = array_chunk($productIds, 50);

        foreach ($batches as $batch) {
            $mutations = [];
            foreach ($batch as $index => $id) {
                $alias = 'add' . $index;
                $mutations[] = $alias . ': tagsAdd(id: "' . $id . '", tags: ["' . implode('","', $tags) . '"]) { node { id } userErrors { field message } }';
            }

            $query = 'mutation { ' . implode(' ', $mutations) . ' }';

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json'
            ])->post($this->getGraphqlUrl($shop), ['query' => $query]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['errors'])) {
                    $results[] = ['batch' => $batch, 'success' => false, 'error' => $data['errors']];
                    continue;
                }
                $hasError = false;
                foreach ($data['data'] as $res) {
                    if (!empty($res['userErrors'])) {
                        $hasError = true;
                        break;
                    }
                }
                $results[] = ['batch' => $batch, 'success' => !$hasError, 'data' => $data['data']];
            } else {
                $results[] = ['batch' => $batch, 'success' => false, 'error' => 'API request failed'];
            }

            usleep(1000000);
        }

        return ['success' => !in_array(false, array_column($results, 'success')), 'results' => $results];
    }

    public function bulkRemoveTags(User $shop, array $productIds, array $tags): array
    {
        $results = [];
        $batches = array_chunk($productIds, 50);

        foreach ($batches as $batch) {
            $mutations = [];
            foreach ($batch as $index => $id) {
                $alias = 'remove' . $index;
                $mutations[] = $alias . ': tagsRemove(id: "' . $id . '", tags: ["' . implode('","', $tags) . '"]) { node { id } userErrors { field message } }';
            }

            $query = 'mutation { ' . implode(' ', $mutations) . ' }';

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json'
            ])->post($this->getGraphqlUrl($shop), ['query' => $query]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['errors'])) {
                    $results[] = ['batch' => $batch, 'success' => false, 'error' => $data['errors']];
                    continue;
                }
                $hasError = false;
                foreach ($data['data'] as $res) {
                    if (!empty($res['userErrors'])) {
                        $hasError = true;
                        break;
                    }
                }
                $results[] = ['batch' => $batch, 'success' => !$hasError, 'data' => $data['data']];
            } else {
                $results[] = ['batch' => $batch, 'success' => false, 'error' => 'API request failed'];
            }

            usleep(1000000);
        }

        return ['success' => !in_array(false, array_column($results, 'success')), 'results' => $results];
    }

    public function addProductsToCollection(User $shop, string $collectionId, array $productIds): array
    {
        $results = [];
        $batches = array_chunk($productIds, 50);

        foreach ($batches as $batch) {
            $query = '
            mutation collectionAddProducts($id: ID!, $productIds: [ID!]!) {
                collectionAddProducts(id: $id, productIds: $productIds) {
                    collection { id }
                    userErrors { field message }
                }
            }';

            $variables = ['id' => $collectionId, 'productIds' => $batch];

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json'
            ])->post($this->getGraphqlUrl($shop), [
                'query' => $query,
                'variables' => $variables
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['errors'])) {
                    $results[] = ['batch' => $batch, 'success' => false, 'error' => $data['errors']];
                    continue;
                }
                $userErrors = $data['data']['collectionAddProducts']['userErrors'] ?? [];
                if (!empty($userErrors)) {
                    $results[] = ['batch' => $batch, 'success' => false, 'error' => $userErrors];
                    continue;
                }
                $results[] = ['batch' => $batch, 'success' => true, 'data' => $data['data']['collectionAddProducts']['collection']];
            } else {
                $results[] = ['batch' => $batch, 'success' => false, 'error' => 'API request failed'];
            }

            usleep(1000000);
        }

        return ['success' => !in_array(false, array_column($results, 'success')), 'results' => $results];
    }

    public function removeProductsFromCollection(User $shop, string $collectionId, array $productIds): array
    {
        $results = [];
        $batches = array_chunk($productIds, 50);

        foreach ($batches as $batch) {
            $query = '
            mutation collectionRemoveProducts($id: ID!, $productIds: [ID!]!) {
                collectionRemoveProducts(id: $id, productIds: $productIds) {
                    job { id }
                    userErrors { field message }
                }
            }';

            $variables = ['id' => $collectionId, 'productIds' => $batch];

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json'
            ])->post($this->getGraphqlUrl($shop), [
                'query' => $query,
                'variables' => $variables
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['errors'])) {
                    $results[] = ['batch' => $batch, 'success' => false, 'error' => $data['errors']];
                    continue;
                }
                $userErrors = $data['data']['collectionRemoveProducts']['userErrors'] ?? [];
                if (!empty($userErrors)) {
                    $results[] = ['batch' => $batch, 'success' => false, 'error' => $userErrors];
                    continue;
                }
                $results[] = ['batch' => $batch, 'success' => true, 'data' => $data['data']['collectionRemoveProducts']['job']];
            } else {
                $results[] = ['batch' => $batch, 'success' => false, 'error' => 'API request failed'];
            }

            usleep(1000000);
        }

        return ['success' => !in_array(false, array_column($results, 'success')), 'results' => $results];
    }

    // Sprint 3 methods
    public function getProductsByRule(User $shop, Rule $rule): array
    {
        $filters = [];
        switch ($rule->apply_to) {
            case 'product_variant':
                // Targets là array IDs, nhưng getProducts không hỗ trợ filter by ID, cần query riêng nếu cần
                // Tạm dùng title '' để lấy all, rồi filter client side
                break;
            case 'tag':
                $filters['tag'] = $rule->targets[0] ?? '';
                break;
            case 'vendor':
                $filters['vendors'] = $rule->targets ?? [];
                break;
            case 'collection':
                $filters['collection'] = $rule->targets[0] ?? '';
                break;
            case 'whole_store':
                break;
        }

        $allProducts = [];
        $after = null;
        do {
            $result = $this->getProducts($shop, 250, $after, $filters);
            $allProducts = array_merge($allProducts, $result['products']);
            $after = $result['pageInfo']['endCursor'] ?? null;
        } while ($result['pageInfo']['hasNextPage'] ?? false);

        $excluded = $rule->excluded_ids ?? [];
        $allProducts = array_filter($allProducts, fn($p) => !in_array($p['id'], $excluded));

        return $allProducts;
    }

    public function bulkUpdatePrices(User $shop, array $productIds, Rule $rule): array
    {
        $results = [];
        $batches = array_chunk($productIds, 50);

        foreach ($batches as $batch) {
            $mutations = '';
            $i = 0;
            foreach ($batch as $productId) {
                $product = $this->getProductById($shop, $productId);
                if (!$product) continue;

                $variants = $product['variants'] ?? [];
                foreach ($variants as $variant) {
                    $metafield = $this->getMetafield($shop, $variant['id'], 'original_prices');
                    $oldPrice = $variant['price'] ?? 0;
                    $oldCompare = $variant['compareAtPrice'] ?? null;

                    if (empty($metafield)) {
                        $this->setMetafield($shop, $variant['id'], 'original_prices', json_encode([
                            'price' => $oldPrice,
                            'compareAtPrice' => $oldCompare
                        ]));
                    }

                    if ($rule->discount_on === 'current_price') {
                        $base = $oldPrice;
                        $newPrice = $this->calculateDiscount($base, $rule->discount_value, $rule->discount_type);
                        $newCompare = $oldCompare ?? $oldPrice;
                    } else {
                        $base = $oldCompare ?? $oldPrice;
                        $newPrice = $this->calculateDiscount($base, $rule->discount_value, $rule->discount_type);
                        $newCompare = $base;
                    }

                    $alias = 'update' . $i++;
                    $mutations .= $alias . ': productVariantUpdate(input: {id: "' . $variant['id'] . '", price: "' . number_format($newPrice, 2) . '", compareAtPrice: "' . number_format($newCompare, 2) . '"}) { productVariant { id } userErrors { field message } } ';
                }
            }

            if (empty($mutations)) continue;

            $query = 'mutation { ' . $mutations . ' }';

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json'
            ])->post($this->getGraphqlUrl($shop), ['query' => $query]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['errors'])) {
                    $results[] = ['batch' => $batch, 'success' => false, 'error' => $data['errors']];
                    continue;
                }
                $hasError = false;
                foreach ($data['data'] as $res) {
                    if (!empty($res['userErrors'])) {
                        $hasError = true;
                        break;
                    }
                }
                $results[] = ['batch' => $batch, 'success' => !$hasError];
            } else {
                $results[] = ['batch' => $batch, 'success' => false, 'error' => 'API request failed'];
            }

            usleep(1000000);
        }

        return ['success' => !in_array(false, array_column($results, 'success')), 'results' => $results];
    }

    public function bulkRestorePrices(User $shop, array $productIds): array
    {
        $results = [];
        $batches = array_chunk($productIds, 50);

        foreach ($batches as $batch) {
            $mutations = '';
            $i = 0;
            foreach ($batch as $productId) {
                $product = $this->getProductById($shop, $productId);
                if (!$product) continue;

                $variants = $product['variants'] ?? [];
                foreach ($variants as $variant) {
                    $metafield = $this->getMetafield($shop, $variant['id'], 'original_prices');
                    if (empty($metafield)) continue;

                    $original = json_decode($metafield['value'], true);
                    $price = $original['price'] ?? $variant['price'];
                    $compare = $original['compareAtPrice'] ?? $variant['compareAtPrice'];

                    $alias = 'restore' . $i++;
                    $mutations .= $alias . ': productVariantUpdate(input: {id: "' . $variant['id'] . '", price: "' . number_format($price, 2) . '", compareAtPrice: "' . number_format($compare, 2) . '"}) { productVariant { id } userErrors { field message } } ';

                    $this->deleteMetafield($shop, $metafield['id']);
                }
            }

            if (empty($mutations)) continue;

            $query = 'mutation { ' . $mutations . ' }';

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shop->password,
                'Content-Type' => 'application/json'
            ])->post($this->getGraphqlUrl($shop), ['query' => $query]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['errors'])) {
                    $results[] = ['batch' => $batch, 'success' => false, 'error' => $data['errors']];
                    continue;
                }
                $hasError = false;
                foreach ($data['data'] as $res) {
                    if (!empty($res['userErrors'])) {
                        $hasError = true;
                        break;
                    }
                }
                $results[] = ['batch' => $batch, 'success' => !$hasError];
            } else {
                $results[] = ['batch' => $batch, 'success' => false, 'error' => 'API request failed'];
            }

            usleep(1000000);
        }

        return ['success' => !in_array(false, array_column($results, 'success')), 'results' => $results];
    }

    public function setMetafield(User $shop, string $ownerId, string $key, string $value, string $type = 'json'): array
    {
        $query = '
        mutation metafieldsSet($metafields: [MetafieldsSetInput!]!) {
            metafieldsSet(metafields: $metafields) {
                metafields {
                    id
                    value
                }
                userErrors {
                    field
                    message
                }
            }
        }';

        $variables = [
            'metafields' => [
                [
                    'ownerId' => $ownerId,
                    'namespace' => 'custom',
                    'key' => $key,
                    'value' => $value,
                    'type' => $type
                ]
            ]
        ];

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->password,
            'Content-Type' => 'application/json'
        ])->post($this->getGraphqlUrl($shop), [
            'query' => $query,
            'variables' => $variables
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (!empty($data['data']['metafieldsSet']['userErrors'])) {
                Log::error('Set Metafield Failed', ['errors' => $data['data']['metafieldsSet']['userErrors']]);
                return ['success' => false, 'error' => $data['data']['metafieldsSet']['userErrors']];
            }
            return ['success' => true, 'metafield' => $data['data']['metafieldsSet']['metafields'][0]];
        }

        return ['success' => false, 'error' => 'API request failed'];
    }

    public function getMetafield(User $shop, string $ownerId, string $key): ?array
    {
        $query = '
        query {
            node(id: "' . $ownerId . '") {
                ... on ProductVariant {
                    metafield(namespace: "custom", key: "' . $key . '") {
                        id
                        value
                    }
                }
            }
        }';

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->password,
            'Content-Type' => 'application/json'
        ])->post($this->getGraphqlUrl($shop), ['query' => $query]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data']['node']['metafield'] ?? null;
        }

        return null;
    }

    public function deleteMetafield(User $shop, string $metafieldId): array
    {
        $query = '
        mutation metafieldDelete($id: ID!) {
            metafieldDelete(id: $id) {
                deletedId
                userErrors {
                    field
                    message
                }
            }
        }';

        $variables = ['id' => $metafieldId];

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->password,
            'Content-Type' => 'application/json'
        ])->post($this->getGraphqlUrl($shop), [
            'query' => $query,
            'variables' => $variables
        ]);

        if ($response->successful()) {
            $data = $response->json();
            if (!empty($data['data']['metafieldDelete']['userErrors'])) {
                Log::error('Delete Metafield Failed', ['errors' => $data['data']['metafieldDelete']['userErrors']]);
                return ['success' => false, 'error' => $data['data']['metafieldDelete']['userErrors']];
            }
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'API request failed'];
    }

    public function getProductById(User $shop, string $id): ?array
    {
        $query = '
        query {
            product(id: "' . $id . '") {
                id
                variants(first: 250) {
                    edges {
                        node {
                            id
                            price
                            compareAtPrice
                        }
                    }
                }
            }
        }';

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $shop->password,
            'Content-Type' => 'application/json'
        ])->post($this->getGraphqlUrl($shop), ['query' => $query]);

        if ($response->successful()) {
            $data = $response->json();
            $product = $data['data']['product'] ?? null;
            if ($product) {
                $product['variants'] = array_map(fn($e) => $e['node'], $product['variants']['edges'] ?? []);
            }
            return $product;
        }

        return null;
    }

    public function getAllShops()
    {
        return User::all();
    }

    public function getShopByDomain(string $domain)
    {
        return User::where('name', $domain)->first();
    }

    /**
     * Tính toán giá sau khi áp dụng giảm giá
     *
     * @param float $base Giá gốc
     * @param float $discountValue Giá trị giảm (phần trăm hoặc số tiền cố định)
     * @param string $discountType Loại giảm giá ('percentage' hoặc 'fix_amount')
     * @return float Giá mới sau khi giảm
     */
    private function calculateDiscount(float $base, float $discountValue, string $discountType): float
    {
        if ($discountType === 'percentage') {
            // Tính giảm giá theo phần trăm, đảm bảo không âm
            $discountAmount = $base * ($discountValue / 100);
            return max(0, $base - $discountAmount);
        } elseif ($discountType === 'fix_amount') {
            // Tính giảm giá bằng số tiền cố định, đảm bảo không âm
            return max(0, $base - $discountValue);
        }

        // Default case, trả về giá gốc nếu discount type không hợp lệ
        return $base;
    }

}

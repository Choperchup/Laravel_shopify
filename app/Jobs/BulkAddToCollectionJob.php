<?php

namespace App\Jobs;

use App\Services\ShopifyGraphQLService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkAddToCollectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shop;
    protected $collectionId;
    protected $productIds;

    public function __construct(User $shop, string $collectionId, array $productIds)
    {
        $this->shop = $shop;
        $this->collectionId = $collectionId;
        $this->productIds = $productIds;
    }

    public function handle(ShopifyGraphQLService $service)
    {
        $result = $service->addProductsToCollection($this->shop, $this->collectionId, $this->productIds);

        if (!$result['success']) {
            // Xử lý lỗi từ API (errors hoặc results)
            $errorData = $result['errors'] ?? ($result['results'][0]['error'] ?? ($result['error'] ?? 'Unknown error'));
            Log::error('Bulk Add to Collection Job Failed', [
                'shop' => $this->shop->name,
                'collectionId' => $this->collectionId,
                'error' => $errorData
            ]);
            $this->fail(new \Exception('Bulk add to collection failed: ' . json_encode($errorData)));
            return;
        }

        Log::info('Bulk Add to Collection Job Completed', [
            'shop' => $this->shop->name,
            'collectionId' => $this->collectionId,
            'products' => count($this->productIds)
        ]);
    }
}

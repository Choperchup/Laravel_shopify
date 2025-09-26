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

class BulkRemoveFromCollectionJob implements ShouldQueue
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
        $result = $service->removeProductsFromCollection($this->shop, $this->collectionId, $this->productIds);

        if (!$result['success']) {
            Log::error('Bulk Remove from Collection Job Failed', [
                'shop' => $this->shop->name,
                'collectionId' => $this->collectionId,
                'error' => $result['error']
            ]);
            $this->fail(new \Exception('Bulk remove from collection failed: ' . json_encode($result['error'])));
            return;
        }

        Log::info('Bulk Remove from Collection Job Completed', [
            'shop' => $this->shop->name,
            'collectionId' => $this->collectionId,
            'products' => count($this->productIds)
        ]);
    }
}

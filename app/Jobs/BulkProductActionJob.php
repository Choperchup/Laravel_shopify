<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ProductGraphQLService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkProductActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /** @var \App\Models\User */
    protected User $shop;

    /** @var string */
    protected string $action;

    /** @var array */
    protected array $productIds;

    /** @var array */
    protected array $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(User $shop, string $action, array $productIds, array $payload = [])
    {
        $this->shop       = $shop;
        $this->action     = $action;
        $this->productIds = $productIds;
        $this->payload    = $payload;
        Log::info("🚀 Job constructed", [
            'action' => $action,
            'productIds' => $productIds,
            'payload' => $payload,
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(ProductGraphQLService $service): void
    {
        Log::info("🟡 BulkProductActionJob running", [
            'action' => $this->action,
            'productIds' => $this->productIds,
            'payload' => $this->payload,
        ]);
        foreach ($this->productIds as $id) {
            switch ($this->action) {
                case 'status':
                    $service->updateProductStatus($this->shop, $id, $this->payload['status']);
                    break;
                case 'add_tags':
                    $service->addTags($this->shop, $id, $this->payload['tags']);
                    break;
                case 'remove_tags':
                    $service->removeTags($this->shop, $id, $this->payload['tags']);
                    break;
                case 'add_collection':
                    Log::info("➡️ Gọi addToCollection", [
                        'collectionId' => $this->payload['collection_id'],
                        'productId'    => $id,
                    ]);
                    $service->addToCollection($this->shop, $this->payload['collection_id'], $id);
                    break;
                case 'remove_collection':
                    Log::info("➡️ Gọi removeFromCollection", [
                        'collectionId' => $this->payload['collection_id'],
                        'productId'    => $id,
                    ]);
                    $service->removeFromCollection($this->shop, $this->payload['collection_id'], $id);
                    break;
            }
        }
    }
    public function failed(\Throwable $exception): void
    {
        Log::error("❌ Job failed", [
            'action' => $this->action,
            'error'  => $exception->getMessage(),
        ]);
    }
}

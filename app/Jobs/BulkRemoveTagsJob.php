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

class BulkRemoveTagsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shop;
    protected $productIds;
    protected $tags;

    public function __construct(User $shop, array $productIds, array $tags)
    {
        $this->shop = $shop;
        $this->productIds = $productIds;
        $this->tags = array_filter($tags);
    }

    public function handle(ShopifyGraphQLService $service)
    {
        if (empty($this->tags)) {
            Log::warning('Bulk Remove Tags Job Skipped: No tags provided', ['shop' => $this->shop->name]);
            return;
        }

        $result = $service->bulkRemoveTags($this->shop, $this->productIds, $this->tags);

        if (!$result['success']) {
            Log::error('Bulk Remove Tags Job Failed', [
                'shop' => $this->shop->name,
                'tags' => $this->tags,
                'error' => $result['results']
            ]);
            $this->fail(new \Exception('Bulk remove tags failed: ' . json_encode($result['results'])));
            return;
        }

        Log::info('Bulk Remove Tags Job Completed', [
            'shop' => $this->shop->name,
            'tags' => $this->tags,
            'products' => count($this->productIds)
        ]);
    }
}

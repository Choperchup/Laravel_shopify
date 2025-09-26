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

class BulkUpdateProductStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shop;
    protected $productIds;
    protected $status;

    public function __construct(User $shop, array $productIds, string $status)
    {
        $this->shop = $shop;
        $this->productIds = $productIds;
        $this->status = strtoupper($status);
    }

    public function handle(ShopifyGraphQLService $service)
    {
        $result = $service->bulkUpdateProductStatus($this->shop, $this->productIds, $this->status);

        if (!$result['success']) {
            Log::error('Bulk Update Status Job Failed', [
                'shop' => $this->shop->name,
                'status' => $this->status,
                'error' => $result['error']
            ]);
            $this->fail(new \Exception('Bulk update status failed: ' . json_encode($result['error'])));
            return;
        }

        Log::info('Bulk Update Status Job Completed', [
            'shop' => $this->shop->name,
            'status' => $this->status,
            'products' => count($this->productIds)
        ]);
    }
}

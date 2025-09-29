<?php

namespace App\Jobs;

use App\Models\Rule;
use App\Services\ShopifyGraphQLService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkRemoveDiscountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rule;

    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
    }

    public function handle(ShopifyGraphQLService $service)
    {
        $shop = User::first();
        $products = $service->getProductsByRule($shop, $this->rule);
        $productIds = array_column($products, 'id');

        $result = $service->bulkRestorePrices($shop, $productIds);

        if ($this->rule->add_tag) {
            BulkRemoveTagsJob::dispatch($shop, $productIds, [$this->rule->add_tag]);
        }

        if (!$result['success']) {
            Log::error('Bulk Remove Discount Failed', [
                'rule_id' => $this->rule->id,
                'error' => $result['results']
            ]);
            $this->fail(new \Exception('Bulk remove discount failed'));
            return;
        }

        Log::info('Bulk Remove Discount Completed', [
            'rule_id' => $this->rule->id,
            'products' => count($productIds)
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::critical('Bulk Remove Discount Job Failed Permanently', [
            'rule_id' => $this->rule->id,
            'error' => $exception->getMessage()
        ]);
    }
}

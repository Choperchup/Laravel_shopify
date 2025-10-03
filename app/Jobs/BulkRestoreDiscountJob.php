<?php

namespace App\Jobs;

use App\Models\Rule;
use App\Models\RuleVariant;
use App\Models\User;
use App\Services\ProductGraphQLService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BulkRestoreDiscountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected User $shop;
    protected Rule $rule;
    protected Collection $ruleVariants;

    public function __construct(User $shop, Rule $rule, Collection $ruleVariants)
    {
        $this->shop = $shop;
        $this->rule = $rule;
        $this->ruleVariants = $ruleVariants;
    }

    public function handle(ProductGraphQLService $service): void
    {
        foreach ($this->ruleVariants as $rv) {
            $update = $service->updateVariantPrices($this->shop, $rv->variant_id, $rv->original_price, $rv->original_compare_at_price);
            if (empty($update['userErrors'])) {
                $rv->delete();
            } else {
                Log::error('Khôi phục biến thể thất bại', ['errors' => $update['userErrors']]);
            }
        }
    }
}

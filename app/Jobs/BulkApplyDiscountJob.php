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
use Illuminate\Support\Facades\Log;

class BulkApplyDiscountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected User $shop;
    protected Rule $rule;
    protected array $variants;

    public function __construct(User $shop, Rule $rule, array $variants)
    {
        $this->shop = $shop;
        $this->rule = $rule;
        $this->variants = $variants;
    }

    public function handle(ProductGraphQLService $service): void
    {
        foreach ($this->variants as $var) {
            $originalPrice = (float) $var['price'];
            $originalCompare = $var['compareAtPrice'] ? (float) $var['compareAtPrice'] : null;
            if ($this->rule->discount_base === 'current_price') {
                $base = $originalPrice;
                $newCompare = $originalCompare ?? $base;
                if ($originalCompare) {
                    $newCompare = $base; // Như trong trường hợp 2
                }
                $discountAmount = $this->rule->discount_type === 'percentage' ? $base * ($this->rule->discount_value / 100) : $this->rule->discount_value;
                $newPrice = max(0, $base - $discountAmount);
            } else {
                $base = $originalCompare ?? $originalPrice;
                $discountAmount = $this->rule->discount_type === 'percentage' ? $base * ($this->rule->discount_value / 100) : $this->rule->discount_value;
                $newPrice = max(0, $base - $discountAmount);
                $newCompare = $base;
            }
            $update = $service->updateVariantPrices($this->shop, $var['id'], $newPrice, $newCompare);
            if (empty($update['userErrors'])) {
                RuleVariant::create([
                    'rule_id' => $this->rule->id,
                    'variant_id' => $var['id'],
                    'product_id' => $var['product_id'],
                    'original_price' => $originalPrice,
                    'original_compare_at_price' => $originalCompare,
                ]);
            } else {
                Log::error('Cập nhật biến thể thất bại', ['errors' => $update['userErrors']]);
            }
        }
    }
}

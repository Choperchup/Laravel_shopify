<?php

namespace App\Console\Commands;

use App\Jobs\BulkApplyDiscountJob;
use App\Jobs\BulkRestoreDiscountJob;
use App\Jobs\BulkProductActionJob;
use App\Models\Rule;
use App\Services\ProductGraphQLService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class ProcessRules extends Command
{
    protected $signature = 'rules:process';
    protected $description = 'Xử lý thời gian bắt đầu/kết thúc của quy tắc';

    public function handle(ProductGraphQLService $service)
    {
        $shop = $service->getFirstShop();
        $now = now();
        $rules = Rule::where('active', true)->whereNull('archived_at')->get();
        foreach ($rules as $rule) {
            $applied = $rule->ruleVariants()->exists();
            $shouldApply = ($rule->start_at <= $now) && (!$rule->end_at || $rule->end_at > $now);
            if ($shouldApply && !$applied) {
                $variants = $service->getMatchingVariants($shop, $rule);
                $batch = Bus::batch([])->dispatch();
                $chunks = array_chunk($variants, 100);
                foreach ($chunks as $chunk) {
                    $batch->add(new BulkApplyDiscountJob($shop, $rule, $chunk));
                }
                $productIds = array_unique(array_column($variants, 'product_id'));
                if ($rule->tags_to_add) {
                    $batch->add(new BulkProductActionJob($shop, 'add_tags', $productIds, ['tags' => $rule->tags_to_add]));
                }
            } elseif (!$shouldApply && $applied) {
                $batch = Bus::batch([])->dispatch();
                $ruleVariants = $rule->ruleVariants;
                $chunks = $ruleVariants->chunk(100);
                foreach ($chunks as $chunk) {
                    $batch->add(new BulkRestoreDiscountJob($shop, $rule, $chunk));
                }
                $productIds = $ruleVariants->pluck('product_id')->unique()->toArray();
                if ($rule->tags_to_add) {
                    $batch->add(new BulkProductActionJob($shop, 'remove_tags', $productIds, ['tags' => $rule->tags_to_add]));
                }
            }
        }
    }
}

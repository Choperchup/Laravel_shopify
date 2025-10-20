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
        Log::info("🔄 [BulkRestoreDiscountJob] Bắt đầu khôi phục giá cho rule ID {$this->rule->id}");

        foreach ($this->ruleVariants as $rv) {
            try {
                // ✅ Nếu original_compare_at_price NULL, chỉ cập nhật lại price thôi
                $update = $service->updateVariantPrices(
                    $this->shop,
                    $rv->product_id,
                    $rv->variant_id,
                    $rv->original_price,
                    $rv->original_compare_at_price
                );

                // Kiểm tra phản hồi từ Shopify
                if (empty($update['userErrors'])) {
                    Log::info("✅ Khôi phục thành công variant {$rv->variant_id}");
                    $rv->delete(); // Xóa record vì rule này đã được phục hồi xong
                } else {
                    $errorMessage = "❌ Lỗi khôi phục variant {$rv->variant_id}: " . json_encode($update['userErrors']);
                    Log::error($errorMessage);
                    throw new \Exception($errorMessage); // Thêm này để job fail nếu error
                }
            } catch (\Exception $e) {
                Log::error("💥 Exception khi khôi phục variant {$rv->variant_id}: " . $e->getMessage());
            }
        }

        // ✅ Sau khi hoàn tất, cập nhật rule status về INACTIVE
        $this->rule->update(['status' => 'INACTIVE']);
        Log::info("🟢 [BulkRestoreDiscountJob] Hoàn tất khôi phục cho rule ID {$this->rule->id}");
    }
}

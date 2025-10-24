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

    protected bool $isExpiryRevert; //Phân biệt revert do expire hay do toggle off

    public function __construct(User $shop, Rule $rule, Collection $ruleVariants, bool $isExpiryRevert = false)
    {
        $this->shop = $shop;
        $this->rule = $rule;
        $this->ruleVariants = $ruleVariants;
        $this->isExpiryRevert = $isExpiryRevert;
    }

    public function handle(ProductGraphQLService $service): void
    {
        Log::info("🔄 [BulkRestoreDiscountJob] Bắt đầu khôi phục giá cho rule ID {$this->rule->id}", [
            'is_expiry_revert' => $this->isExpiryRevert,
            'current_status' => $this->rule->status
        ]);

        $successCount = 0;

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
                    $successCount++;
                } else {
                    $errorMessage = "❌ Lỗi khôi phục variant {$rv->variant_id}: " . json_encode($update['userErrors']);
                    Log::error($errorMessage);
                    throw new \Exception($errorMessage); // Thêm này để job fail nếu error
                }
            } catch (\Exception $e) {
                Log::error("💥 Exception khi khôi phục variant {$rv->variant_id}: " . $e->getMessage());
            }
        }
        // ✅ QUAN TRỌNG: CHỈ set INACTIVE khi KHÔNG phải revert do expire
        if (!$this->isExpiryRevert) {
            // Revert do toggle off → set INACTIVE
            $this->rule->update(['status' => 'INACTIVE']);
            Log::info("🟢 [Toggle Off] Hoàn tất khôi phục. Status → INACTIVE");
        } else {
            // Revert do expire → GIỮ ACTIVE, chỉ log
            Log::info("🟢 [Expiry] Hoàn tất khôi phục. Status GIỮ ACTIVE (vẫn bật)");
        }

        Log::info("📊 Kết quả: {$successCount}/{$this->ruleVariants->count()} variants khôi phục thành công");
    }
}

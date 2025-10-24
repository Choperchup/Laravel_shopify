<?php

namespace App\Console\Commands;

use App\Http\Controllers\RulesGraphQLController;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckRuleStatus extends Command
{
    protected $signature = 'rules:check-status';
    protected $description = 'Tự động kiểm tra và cập nhật trạng thái các quy tắc theo thời gian';

    public function handle(RulesGraphQLController $rulesController): int
    {
        $now = Carbon::now();
        $shop = User::first();

        if (!$shop) {
            $this->error('Không tìm thấy shop nào trong hệ thống.');
            return 1;
        }

        $this->info("🔍 Bắt đầu quét lúc: " . $now->format('H:i:s d/m/Y') . " (Tz: " . $now->timezoneName . ")");


        // --- 0. SẮP HẾT HẠN --- 
        $soonToExpire = Rule::where('status', 'ACTIVE')
            ->whereNotNull('end_at')
            ->whereBetween('end_at', [$now, $now->copy()->addMinutes(5)]) // ⏱ trong 5 phút tới
            ->get();

        if ($soonToExpire->isNotEmpty()) {
            $this->info("⚠️ Có {$soonToExpire->count()} quy tắc sắp hết hạn trong 5 phút:");
            foreach ($soonToExpire as $rule) {
                $remaining = $rule->end_at->diffForHumans($now, ['parts' => 2, 'short' => true]);
                $this->info("   → Rule #{$rule->id}: {$rule->name} (hết hạn sau {$remaining})");
            }
        } else {
            $this->info("🕐 Không có quy tắc nào sắp hết hạn trong 5 phút tới.");
        }

        // --- 1. KÍCH HOẠT (GIỮ NGUYÊN) ---
        $rulesToActivate = Rule::where('is_enabled', true)
            ->where('status', 'SCHEDULED')
            ->where('start_at', '<=', $now)
            ->get();

        if ($rulesToActivate->isNotEmpty()) {
            $this->info("✅ Tìm thấy {$rulesToActivate->count()} quy tắc cần KÍCH HOẠT");
            foreach ($rulesToActivate as $rule) {
                $this->info("   → Rule #{$rule->id}: {$rule->name}");
                $rulesController->dispatchActivationJobs($shop, $rule);
            }
        }

        // --- 2. EXPIRE (✅ SỬA: CHỈ ACTIVE) ---
        $rulesToExpire = Rule::where('status', 'ACTIVE')  // ✅ CHỈ ACTIVE, KHÔNG ACTIVATING
            ->whereNotNull('end_at')
            ->where('end_at', '<', $now)
            ->get();

        if ($rulesToExpire->isNotEmpty()) {
            $this->info("⏰ Tìm thấy {$rulesToExpire->count()} quy tắc EXPIRE");
            foreach ($rulesToExpire as $rule) {
                $this->info("   → Rule #{$rule->id}: {$rule->name} (end: " . $rule->end_at->format('H:i') . ")");
                $rulesController->dispatchDeactivationJobs($shop, $rule, true);
            }
        }

        $this->info('✅ Hoàn tất quét.');
        return 0;
    }
}

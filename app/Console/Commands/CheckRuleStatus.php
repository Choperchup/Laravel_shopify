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
        // Lấy thời gian hiện tại theo đúng múi giờ đã cấu hình trong config/app.php
        $now = Carbon::now();
        $shop = User::first();

        if (!$shop) {
            $this->error('Không tìm thấy shop nào trong hệ thống.');
            return 1;
        }

        $this->info("Bắt đầu quét lúc: " . $now->toDateTimeString() . " (Múi giờ: " . $now->timezoneName . ")");

        // --- 1. Kích hoạt các rule đã được bật và đến giờ chạy ---
        $rulesToActivate = Rule::where('is_enabled', true)
            ->where('status', 'SCHEDULED')
            ->where('start_at', '<=', $now) // So sánh bây giờ đã chính xác
            ->get();

        if ($rulesToActivate->isNotEmpty()) {
            $this->info("Tìm thấy " . $rulesToActivate->count() . " quy tắc cần kích hoạt.");
            foreach ($rulesToActivate as $rule) {
                $this->info(" -> Đang xử lý Rule ID: {$rule->id}");
                $rulesController->dispatchActivationJobs($shop, $rule);
            }
        }

        // --- 2. Hủy kích hoạt các rule đã hết hạn ---
        $rulesToExpire = Rule::whereIn('status', ['ACTIVE', 'ACTIVATING'])
            ->whereNotNull('end_at')
            ->where('end_at', '<', $now) // So sánh bây giờ đã chính xác
            ->get();

        if ($rulesToExpire->isNotEmpty()) {
            $this->info("Tìm thấy " . $rulesToExpire->count() . " quy tắc đã hết hạn.");
            foreach ($rulesToExpire as $rule) {
                $this->info(" -> Đang xử lý Rule ID: {$rule->id}");
                $rulesController->dispatchDeactivationJobs($shop, $rule);
            }
        }

        $this->info('Hoàn tất việc quét.');
        return 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Services\ProductGraphQLService;
use Illuminate\Support\Str;
use App\Models\User;

class Rule extends Model
{
    protected $casts = [
        'apply_to_targets' => 'array',
        'exclude_products' => 'array',
        'tags_to_add' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'discount_value',
        'discount_type',
        'discount_base',
        'apply_to_type',
        'apply_to_targets',
        'exclude_products',
        'start_at',
        'end_at',
        'tags_to_add',
        'active',
        'archived_at',
    ];

    /**
     * Quan hệ với RuleVariant
     */
    public function ruleVariants()
    {
        return $this->hasMany(RuleVariant::class);
    }

    /**
     * Hiển thị trạng thái
     */
    public function getStatusDisplayAttribute(): string
    {
        $now = Carbon::now();
        if ($this->archived_at) return 'Đã lưu trữ';
        if (!$this->active) return 'Không hoạt động';
        if ($this->start_at && $this->start_at > $now) return 'Bắt đầu vào ' . $this->start_at->format('h:i a, d M');
        if ($this->end_at && $this->end_at < $now) return 'Dừng vào ' . $this->end_at->format('h:i a, d M');
        return 'Hoạt động từ ' . ($this->start_at ? $this->start_at->format('h:i a, d M') : 'bây giờ');
    }

    /**
     * Hiển thị điều kiện áp dụng
     */
    public function getConditionsDisplayAttribute(): array
    {
        $lines = [];

        // Đảm bảo mảng apply_to_targets và exclude_products luôn hợp lệ
        $applyToTargets = $this->normalizeToArray($this->apply_to_targets);
        $excludeProducts = $this->normalizeToArray($this->exclude_products);

        // Lấy shop an toàn
        $shop = $this->getShop();

        // Giảm giá
        $currency = $shop ? app(ProductGraphQLService::class)->getCurrency($shop) : '';
        $discount = $this->discount_value
            . ($this->discount_type === 'percentage' ? '%' : ' ' . $currency);

        $lines[] = 'Giảm giá: ' . $discount;

        // Số lượng mục áp dụng
        $count = count($applyToTargets);
        $typePlural = Str::plural($this->apply_to_type ?? 'item', $count);
        $lines[] = $count . ' ' . ucfirst($typePlural);

        // Ngày bắt đầu & kết thúc
        if ($this->start_at) $lines[] = 'Bắt đầu: ' . $this->start_at->format('h:i a, d M');
        if ($this->end_at) $lines[] = 'Kết thúc: ' . $this->end_at->format('h:i a, d M');

        // Loại trừ sản phẩm
        $lines[] = 'Loại trừ: ' . count($excludeProducts) . ' Sản phẩm';

        return $lines;
    }

    /**
     * Đảm bảo giá trị luôn trả về mảng
     */
    private function normalizeToArray($value): array
    {
        if (is_array($value)) return $value;

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Lấy shop hiện tại (an toàn, có thể null)
     */
    protected function getShop(): ?User
    {
        // Nếu lưu shop object hoặc user id trong session
        if ($shopId = session('shopify_shop_id')) {
            return User::find($shopId);
        }

        return null; // fallback an toàn
    }
}

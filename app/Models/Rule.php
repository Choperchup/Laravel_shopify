<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Services\ProductGraphQLService;

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

    public function ruleVariants()
    {
        return $this->hasMany(RuleVariant::class);
    }

    public function getStatusDisplayAttribute(): string
    {
        $now = Carbon::now();
        if ($this->archived_at) return 'Đã lưu trữ';
        if (!$this->active) return 'Không hoạt động';
        if ($this->start_at && $this->start_at > $now) return 'Bắt đầu vào ' . $this->start_at->format('h:i a, d M');
        if ($this->end_at && $this->end_at < $now) return 'Dừng vào ' . $this->end_at->format('h:i a, d M');
        return 'Hoạt động từ ' . ($this->start_at ? $this->start_at->format('h:i a, d M') : 'bây giờ');
    }

    public function getConditionsDisplayAttribute(): array
    {
        $lines = [];
        $discount = $this->discount_value . ($this->discount_type === 'percentage' ? '%' : ' ' . app(ProductGraphQLService::class)->getCurrency($this->getShop()));
        $lines[] = 'Giảm giá: ' . $discount;
        $count = count($this->apply_to_targets ?? []);
        $typePlural = \Illuminate\Support\Str::plural($this->apply_to_type, $count);
        $lines[] = $count . ' ' . ucfirst($typePlural);
        if ($this->start_at) $lines[] = 'Bắt đầu: ' . $this->start_at->format('h:i a, d M');
        if ($this->end_at) $lines[] = 'Kết thúc: ' . $this->end_at->format('h:i a, d M');
        $lines[] = 'Loại trừ: ' . count($this->exclude_products ?? []) . ' Sản phẩm';
        return $lines;
    }

    protected function getShop(): User
    {
        return $this->getFirstShop(); // Hoặc từ phiên/lưu trữ
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'discount_value',
        'discount_type',
        'discount_on',
        'apply_to',
        'targets',
        'summary',
        'add_tag',
        'start_time',
        'end_time',
        'excluded_count',
        'excluded_ids',
        'status',
        'archived'
    ];

    protected $casts = [
        'targets' => 'array',
        'excluded_ids' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'archived' => 'boolean',
    ];

    public function isActive(): bool
    {
        $now = Carbon::now();
        return $this->status === 'on' && $this->start_time <= $now && ($this->end_time === null || $this->end_time > $now);
    }
}

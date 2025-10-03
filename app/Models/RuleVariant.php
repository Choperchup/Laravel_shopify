<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleVariant extends Model
{
    protected $fillable = [
        'rule_id',
        'variant_id',
        'product_id',
        'original_price',
        'original_compare_at_price'
    ];
}

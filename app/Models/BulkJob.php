<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkJob extends Model
{
    protected $fillable = ['status', 'action', 'product_ids', 'payload', 'result', 'error'];

    protected $casts = [
        'product_ids' => 'array',
        'payload'     => 'array',
        'result'      => 'array',
    ];
}

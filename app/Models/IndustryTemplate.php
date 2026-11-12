<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndustryTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'badge',
        'description',
        'raw_idea',
        'suggested_price_min',
        'suggested_price_max',
        'currency',
        'estimated_weeks',
        'target_platforms',
        'suggested_modules',
    ];

    protected $casts = [
        'target_platforms' => 'array',
        'suggested_modules' => 'array',
    ];
}

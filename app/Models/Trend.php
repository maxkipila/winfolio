<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trend extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'weekly_growth' => 'decimal:2',
        'annual_growth' => 'decimal:2',
        'calculated_at' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

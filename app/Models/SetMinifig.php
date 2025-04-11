<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SetMinifig extends Pivot
{
    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'minifig_id'
    ];

    public function set()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function minifig()
    {
        return $this->belongsTo(Product::class, 'minifig_id');
    }
}

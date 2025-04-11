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
    public function minifigs()
    {
        return $this->belongsToMany(Product::class, 'set_minifigs', 'parent_id', 'minifig_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function sets()
    {
        return $this->belongsToMany(Product::class, 'set_minifigs', 'minifig_id', 'parent_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function minifig()
    {
        return $this->belongsTo(Product::class, 'minifig_id');
    }
}

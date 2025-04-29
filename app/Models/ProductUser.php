<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductUser extends Pivot
{

    protected $table = 'product_user';
    public $incrementing = true;


    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

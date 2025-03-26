<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    use HasFactory;

    public function theme()
    {
        return $this->belongsTo(\App\Models\Theme::class);
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'product_user')
            ->withTimestamps();
    }
}

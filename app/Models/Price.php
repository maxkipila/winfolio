<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;
    protected $guarded = [];/* 
    protected $primaryKey = 'price_id';
    protected $keyType = 'string';
    public $incrementing = false; */

    public function set()
    {
        return $this->belongsTo(Set::class);
    }

    public function minifig()
    {
        return $this->belongsTo(Minifig::class, 'minifig_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minifig extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $primaryKey = 'fig_num';
    protected $keyType = 'string';
    public $incrementing = false;

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

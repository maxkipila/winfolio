<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minifig extends Model
{
    use HasFactory;

    protected $guarded = [];
    /*   protected $primaryKey = 'fig_num'; */
    /*  protected $keyType = 'string';
    public $incrementing = false; */

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

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'minifig_user', 'minifig_id', 'user_id', 'fig_num', 'id');
    }
}

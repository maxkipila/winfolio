<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sets()
    {
        return $this->hasMany(Set::class);
    }
    public function minifigs()
    {
        return $this->hasMany(Minifig::class);
    }
}

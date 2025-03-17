<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{

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

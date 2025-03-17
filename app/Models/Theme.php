<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;


    public function parent()
    {
        return $this->belongsTo(Theme::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Theme::class, 'parent_id');
    }

    public function sets()
    {
        return $this->hasMany(Set::class);
    }
}

<?php

namespace App\Models;

use GuzzleHttp\Psr7\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'set_num';
    protected $keyType = 'string';
    public $incrementing = false;

    use HasFactory;

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}

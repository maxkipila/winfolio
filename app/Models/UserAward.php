<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAward extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'earned_at' => 'datetime',
        'notified' => 'boolean',
    ];

    /**
     * Vztah k uživateli, který získal odznak.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vztah k odznaku, který byl udělen.
     */
    public function award()
    {
        return $this->belongsTo(Award::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserAward extends Pivot
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'user_awards';
    protected $casts = [
        'earned_at' => 'datetime',
        'notified' => 'boolean',
        'claimed_at' => 'datetime',
    ];
    protected $dates = [
        'earned_at',
        'claimed_at'
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
    public function isClaimed(): bool
    {
        return !is_null($this->claimed_at);
    }
    public function claim(User $user)
    {

        if ($this->claimed_at !== null) {
            throw new \Exception('Badge has already been claimed');
        }
        $this->user_id = $user->id;
        $this->claimed_at = now();
        $this->save();

        return $this;
    }
}

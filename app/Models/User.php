<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }


    public function minifig()
    {
        return $this->belongsTo(Minifig::class, 'minifig_id', 'id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function awards()
    {
        return $this->belongsToMany(Award::class)->withTimestamps();
    }
    public function sets()
    {
        return $this->belongsToMany(Set::class, 'set_user', 'user_id', 'set_id');
    }

    public function minifigs()
    {
        return $this->belongsToMany(Minifig::class, 'minifig_user', 'user_id', 'minifig_id');
    }

    public function products()
    {
        return $this->belongsToMany(\App\Models\Product::class, 'product_user')
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

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

    public function generateTwoFactorCode(): void
    {
        if ($this->two_fa_expires_at && now()->lt($this->two_fa_expires_at)) {
            return;
        }

        $this->timestamps = false;
        $code = 123456;
        $this->two_fa_code = Hash::make($code);
        $this->two_fa_expires_at = now()->addMinutes(10);
        $this->save();
        // $this->sendTwoFactorCode($code);
    }


    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function favourites(): HasMany
    {
        return $this->hasMany(Favourite::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot([
                'purchase_day',
                'purchase_month',
                'purchase_year',
                'purchase_price',
                'currency',
                'condition'
            ])
            ->withTimestamps();
    }
    public function getPortfolioValue()
    {
        return $this->products->sum(function ($product) {
            return $product->price ? $product->price->value : 0;
        });
    }
    public function userAwards()
    {
        return $this->hasMany(UserAward::class);
    }

    //ziska vsechny recordy
    public function records(): HasMany
    {
        return $this->hasMany(UserRecord::class);
    }
    //ziska vsechny recordy podle typu
    public function getRecord(string $type): ?UserRecord
    {
        return $this->records()->where('record_type', $type)->first();
    }

    public function awards()
    {
        return $this->belongsToMany(Award::class, 'user_awards')
            ->withPivot(['earned_at', 'notified', 'count', 'value', 'percentage'])
            ->withTimestamps();
    }
}

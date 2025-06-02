<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Paradigma\Pictura\Traits\HasWebp;
use Spatie\MediaLibrary\HasMedia;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasWebp;

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

    public function generateTwoFactorCode()
    {
        if ($this->two_fa_expires_at && now()->lt($this->two_fa_expires_at)) {
            return;
        }

        $this->timestamps = false;
        $code = random_int(100000, 999999);
        $this->two_fa_code = Hash::make($code);
        $this->two_fa_expires_at = now()->addMinutes(10);
        $this->save();

        return $code;
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
            ->using(ProductUser::class)
            ->withPivot([
                'id',
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

    /*  public function awards()
    {
        return $this->belongsToMany(Award::class, 'user_awards')
            ->withPivot(['earned_at', 'notified', 'count', 'value', 'percentage'])
            ->withTimestamps();
    } */
    public function awards()
    {
        return $this->belongsToMany(
            Award::class,
            'user_awards',
            'user_id',
            'award_id'
        )
            ->withPivot(['earned_at', 'claimed_at', 'notified', 'user_description', 'count', 'value', 'percentage'])
            ->withTimestamps();
    }

    public function thumbnail(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getFirstFile('thumbnail'),
            set: fn($value) => $this->replaceImages($value ?? []),
        );
    }
}

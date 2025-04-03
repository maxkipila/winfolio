<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $guarded = [];

    use HasFactory;

    public function theme()
    {
        return $this->belongsTo(\App\Models\Theme::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'product_user',
            'product_id',
            'user_id'
        );
    }
    public function prices()
    {
        return $this->hasMany(Price::class);
    }
    public function latest_price()
    {
        return $this->hasOne(Price::class)->latestOfMany();
    }

    public function price()
    {
        return $this->hasOne(Price::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
    public function news()
    {
        return $this->hasMany(News::class);
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
    public function awards(): BelongsToMany
    {
        return $this->belongsToMany(Award::class, 'user_awards')
            ->withPivot(['value', 'count', 'percentage', 'notified', 'earned_at'])
            ->withTimestamps();
    }
}

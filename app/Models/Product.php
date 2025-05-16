<?php

namespace App\Models;

use App\Traits\HasResource;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Paradigma\Pictura\Traits\HasWebp;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;


class Product extends Model implements HasMedia
{
    use HasWebp, HasResource, HasFactory;


    protected $guarded = [];

    public function theme()
    {
        return $this->belongsTo(\App\Models\Theme::class);
    }
    /* 
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'product_user',
            'product_id',
            'user_id'

        );
    } */
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->using(ProductUser::class)
            ->withPivot([
                'id',
                'user_id',
                'product_id',
                'product_user',
                'quantity',
                'purchase_day',
                'purchase_month',
                'purchase_year',
                'purchase_price',
                'currency',
                'condition'
            ])
            ->withTimestamps();
    }

    // zpetna kompatibilita v pripade, ze se stale ocekava img_uel
    public function getImgUrlAttribute()
    {
        $media = $this->getFirstMedia('images');
        if ($media) {
            return $media->getUrl();
        }

        if ($this->product_type === 'set') {
            return "https://cdn.rebrickable.com/media/sets/{$this->product_num}.jpg";
        } elseif ($this->product_type === 'minifig') {
            return "https://cdn.rebrickable.com/media/minifigs/{$this->product_num}.jpg";
        }

        return null;
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }
    public function latest_price(): HasOne
    {
        return $this->hasOne(Price::class)->latestOfMany();
    }

    public function images(): Attribute
    {
        return new Attribute(
            get: fn() => $this->getFiles('images'),
            set: fn($value) => $this->addImages($value ?? []),
        );
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

    public function minifigs()
    {
        return $this->belongsToMany(Product::class, 'set_minifigs', 'parent_id', 'minifig_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function sets()
    {
        return $this->belongsToMany(Product::class, 'set_minifigs', 'minifig_id', 'parent_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}

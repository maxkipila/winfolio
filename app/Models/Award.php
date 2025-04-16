<?php

namespace App\Models;

use App\Enums\AwardConditionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Award extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'condition_type' => AwardConditionType::class,
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(AwardCondition::class);
    }
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_awards')
            ->withPivot(['earned_at', 'notified', 'count', 'value', 'percentage'])
            ->withTimestamps();
    }

    // Pokud používáte userAwards(), přidejte také tuto metodu
    public function userAwards()
    {
        return $this->hasMany(UserAward::class);
    }
    public function claim(User $user)
    {
        $this->user_id = $user->id;
        $this->claimed_at = now();
        $this->save();

        return $this;
    }
}

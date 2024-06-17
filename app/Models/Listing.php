<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'host_id', 'privacy_id', 'name', 'thumbnail', 'neighbourhood', 'latitude', 'longtitude',
        'price', 'adult', 'child', 'minimum_nights', 'number_of_reviews', 'new_discount', 'weekly_discount',
        'monthly_discount', 'description', 'status', 'host_name'
    ];
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'amenity_listing');
    }
    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class);
    }
    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class);
    }
    public function privacy(): BelongsTo
    {
        return $this->belongsTo(Privacy::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}

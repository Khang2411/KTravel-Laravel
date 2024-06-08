<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'user1_id', 'user2_id', 'listing_id'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
    public function messageContents(): HasMany
    {
        return $this->hasMany(MessageContent::class);
    }
    public function userFrom(): BelongsTo
    {
        return $this->belongsTo(User::class,'user1_id');
    }
    public function userTo(): BelongsTo
    {
        return $this->belongsTo(User::class,'user2_id');
    }
}

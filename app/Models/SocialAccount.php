<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $fillable = ['user_id', 'provider', 'provider_id', 'name', 'avatar', 'access_token', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function needsRefresh(int $days = 5)
    {
        return $this->expires_at !== null && $this->expires_at->lessThanOrEqualTo(now()->addDays($days));
    }
}

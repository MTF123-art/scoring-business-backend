<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
    protected $fillable = [
        'social_account_id',
        'provider',
        'date',
        'followers',
        'media_count',
        'total_likes',
        'total_comments',
        'total_shares',
        'total_reach',
        'engagement_rate',
        'reach_ratio',
        'engagement_per_post',
        'post_count',
    ];

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class);
    }
}

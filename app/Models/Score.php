<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'business_id',
        'date',
        'instagram_score',
        'facebook_score',
        'final_score',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
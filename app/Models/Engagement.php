<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Engagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'platform',
        'likes',
        'comments',
        'shares',
        'clicks',
        'impressions',
        'reach',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
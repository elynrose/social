<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'publish_at',
        'time_zone',
        'status',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'scheduled',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
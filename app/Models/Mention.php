<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Mention extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'platform',
        'content',
        'author',
        'posted_at',
        'sentiment',
        'is_tagged',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'is_tagged' => 'boolean',
    ];
}
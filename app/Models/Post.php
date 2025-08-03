<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class Post extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'social_account_id',
        'campaign_id',
        'status',
        'content',
        'media_path',
        'variant_of',
        // external_id stores the remote ID returned by the social
        // platform when the post is published.  This allows us to
        // query analytics and mentions for the post.
        'external_id',
        // Accessibility fields
        'alt_text',
        'captions_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function schedule()
    {
        return $this->hasOne(ScheduledPost::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function engagement()
    {
        return $this->hasOne(Engagement::class);
    }

    public function mentions()
    {
        return $this->hasMany(Mention::class);
    }

    public function scheduledPosts()
    {
        return $this->hasMany(ScheduledPost::class);
    }

    // Helper methods for analytics
    public function getEngagementRateAttribute()
    {
        if (!$this->engagement) {
            return 0;
        }
        
        $totalEngagement = ($this->engagement->likes ?? 0) + 
                          ($this->engagement->comments ?? 0) + 
                          ($this->engagement->shares ?? 0);
        
        $followers = $this->socialAccount->followers_count ?? 1;
        
        return $followers > 0 ? round(($totalEngagement / $followers) * 100, 2) : 0;
    }

    public function getTotalEngagementAttribute()
    {
        if (!$this->engagement) {
            return 0;
        }
        
        return ($this->engagement->likes ?? 0) + 
               ($this->engagement->comments ?? 0) + 
               ($this->engagement->shares ?? 0);
    }

    public function getReachAttribute()
    {
        return $this->engagement->reach ?? 0;
    }

    public function getImpressionsAttribute()
    {
        return $this->engagement->impressions ?? 0;
    }
}
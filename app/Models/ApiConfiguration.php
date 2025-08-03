<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'platform',
        'client_id',
        'client_secret',
        'redirect_uri',
        'scopes',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'scopes' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    protected $hidden = [
        'client_secret'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getClientSecretAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    public function setClientSecretAttribute($value)
    {
        $this->attributes['client_secret'] = $value ? encrypt($value) : null;
    }

    public function getScopesAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }

    public function setScopesAttribute($value)
    {
        $this->attributes['scopes'] = json_encode($value ?: []);
    }

    public function getSettingsAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }

    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = json_encode($value ?: []);
    }

    public static function getDefaultScopes($platform)
    {
        $defaultScopes = [
            'facebook' => [
                'pages_read_engagement',
                'pages_manage_posts',
                'pages_show_list',
                'pages_read_user_content',
                'pages_manage_metadata'
            ],
            'twitter' => [
                'tweet.read',
                'tweet.write',
                'users.read',
                'offline.access',
                'dm.read',
                'dm.write'
            ],
            'linkedin' => [
                'w_member_social',
                'r_liteprofile',
                'r_organization_social',
                'w_organization_social'
            ],
            'instagram' => [
                'basic',
                'pages_show_list',
                'pages_read_engagement',
                'instagram_basic',
                'instagram_content_publish'
            ],
            'youtube' => [
                'https://www.googleapis.com/auth/youtube.upload',
                'https://www.googleapis.com/auth/youtube.readonly',
                'https://www.googleapis.com/auth/youtube.force-ssl'
            ],
            'tiktok' => [
                'user.info.basic',
                'video.list',
                'video.upload',
                'video.publish'
            ]
        ];

        return $defaultScopes[$platform] ?? [];
    }

    public static function getPlatformOptions()
    {
        return [
            'facebook' => 'Facebook',
            'twitter' => 'Twitter/X',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok'
        ];
    }

    public function isConfigured()
    {
        return !empty($this->client_id) && !empty($this->client_secret);
    }
} 
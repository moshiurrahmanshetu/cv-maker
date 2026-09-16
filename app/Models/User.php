<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * User's CVs.
     */
    public function cvs()
    {
        return $this->hasMany(Cv::class);
    }

    /**
     * User's Career Documents.
     */
    public function documents()
    {
        return $this->hasMany(Cv::class);
    }

    /**
     * User's AI Assistant generation logs.
     */
    public function aiUsageLogs()
    {
        return $this->hasMany(AiUsageLog::class);
    }

    /**
     * User's purchase orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * User's active product/template entitlements.
     */
    public function entitlements()
    {
        return $this->hasMany(UserEntitlement::class);
    }

    /**
     * Check if user has access to a specific template.
     */
    public function hasAccessToTemplate(?CvTemplate $template = null): bool
    {
        if ($this->isAdmin() || (bool)($this->is_premium ?? false)) {
            return true;
        }

        if (!$template || !$template->is_premium) {
            return true;
        }

        return $this->entitlements()
            ->where('status', 'active')
            ->where(function ($q) use ($template) {
                $q->where('cv_template_id', $template->id)
                  ->orWhere('entitlement_type', 'all_access');
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Check if user has access to premium templates and downloads.
     */
    public function hasPremiumAccess(?CvTemplate $template = null): bool
    {
        if ($template) {
            return $this->hasAccessToTemplate($template);
        }

        return $this->isAdmin() || (bool)($this->is_premium ?? false) || $this->entitlements()->where('status', 'active')->exists();
    }
}



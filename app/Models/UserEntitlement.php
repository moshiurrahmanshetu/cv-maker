<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEntitlement extends Model
{
    use HasFactory;

    protected $table = 'user_entitlements';

    protected $fillable = [
        'user_id',
        'product_id',
        'cv_template_id',
        'order_id',
        'entitlement_type',
        'status',
        'granted_at',
        'expires_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CvTemplate::class, 'cv_template_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getItemTypeAttribute(): string
    {
        return $this->entitlement_type ?? 'template';
    }

    public function getItemIdAttribute()
    {
        return $this->cv_template_id ?? $this->product_id;
    }

    /**
     * Check if entitlement is currently active and not expired.
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}

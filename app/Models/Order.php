<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'currency',
        'status',
        'payment_provider',
        'transaction_id',
        'payment_details',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_details' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(UserEntitlement::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Badge CSS class for UI status indicators.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'bg-success text-white',
            'pending' => 'bg-warning text-dark',
            'failed' => 'bg-danger text-white',
            'cancelled' => 'bg-secondary text-white',
            'refunded' => 'bg-info text-dark',
            default => 'bg-light text-dark',
        };
    }

    /**
     * Formatted amount display string.
     */
    public function getFormattedAmountAttribute(): string
    {
        return $this->getFormattedTotal();
    }

    public function getFormattedTotal(): string
    {
        $symbol = match ($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $this->currency . ' ',
        };

        return $symbol . number_format($this->total_amount, 2);
    }
}

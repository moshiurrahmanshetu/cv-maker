<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CvTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'key',
        'description',
        'preview_image',
        'is_premium',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Category relationship.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'category_id');
    }

    /**
     * CVs created using this template.
     */
    public function cvs(): HasMany
    {
        return $this->hasMany(Cv::class, 'template_id');
    }

    /**
     * Scope for active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for free templates.
     */
    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Scope for premium templates.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Accessor for full preview image URL.
     */
    public function getPreviewImageUrlAttribute(): string
    {
        if ($this->preview_image) {
            if (str_starts_with($this->preview_image, 'http://') || str_starts_with($this->preview_image, 'https://')) {
                return $this->preview_image;
            }
            if (str_starts_with($this->preview_image, 'images/')) {
                return asset($this->preview_image);
            }
            return Storage::disk('public')->url($this->preview_image);
        }

        // Fallback default preview image
        return asset('images/templates/' . $this->key . '.svg');
    }
}

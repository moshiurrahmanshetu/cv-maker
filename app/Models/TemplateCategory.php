<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get templates belonging to this category.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(CvTemplate::class, 'category_id')->orderBy('sort_order');
    }

    /**
     * Get active templates belonging to this category.
     */
    public function activeTemplates(): HasMany
    {
        return $this->hasMany(CvTemplate::class, 'category_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }
}

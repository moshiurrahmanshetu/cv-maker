<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
        'sort_order',
        'configuration',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'configuration' => 'array',
    ];

    /**
     * Career Documents created with this document type.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Cv::class, 'document_type_id');
    }

    /**
     * CVs alias for documents.
     */
    public function cvs(): HasMany
    {
        return $this->hasMany(Cv::class, 'document_type_id');
    }

    /**
     * Compatible templates for this document type.
     */
    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(CvTemplate::class, 'document_type_template', 'document_type_id', 'cv_template_id')
            ->withTimestamps();
    }

    /**
     * Active compatible templates.
     */
    public function activeTemplates(): BelongsToMany
    {
        return $this->templates()->where('cv_templates.is_active', true)->orderBy('cv_templates.sort_order');
    }

    /**
     * Scope for active document types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this document type is a CV/Resume format.
     */
    public function isCvBased(): bool
    {
        return in_array($this->slug, [
            'standard-cv',
            'ats-cv',
            'usa-resume',
            'australia-cv',
            'europe-cv',
        ]);
    }

    /**
     * Check if this document type is a Letter format.
     */
    public function isLetterBased(): bool
    {
        return in_array($this->slug, [
            'cover-letter',
            'motivation-letter',
        ]);
    }
}

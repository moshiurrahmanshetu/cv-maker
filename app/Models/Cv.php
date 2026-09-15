<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cv extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'summary',
        'status',
        'template_key',
        'primary_color',
        'font_family',
        'completion_percentage',
    ];

    protected $casts = [
        'completion_percentage' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function personalInfo(): HasOne
    {
        return $this->hasOne(CvPersonalInfo::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(CvExperience::class)->orderBy('sort_order')->orderBy('start_date', 'desc');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(CvEducation::class)->orderBy('sort_order')->orderBy('start_date', 'desc');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(CvSkill::class)->orderBy('sort_order');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(CvLanguage::class)->orderBy('sort_order');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(CvCertification::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(CvProject::class)->orderBy('sort_order');
    }

    public function awards(): HasMany
    {
        return $this->hasMany(CvAward::class)->orderBy('sort_order');
    }

    public function references(): HasMany
    {
        return $this->hasMany(CvReference::class)->orderBy('sort_order');
    }

    public function customSections(): HasMany
    {
        return $this->hasMany(CvCustomSection::class)->orderBy('sort_order');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Compute completion percentage based on filled sections.
     */
    public function calculateCompletion(): int
    {
        $score = 0;
        // Title & basic info (20)
        if (!empty($this->title)) $score += 15;
        if (!empty($this->summary)) $score += 10;

        // Personal Info (25)
        if ($this->personalInfo && !empty($this->personalInfo->full_name) && !empty($this->personalInfo->email)) {
            $score += 25;
        }

        // Experiences (20)
        if ($this->experiences()->count() > 0) {
            $score += 20;
        }

        // Educations (15)
        if ($this->educations()->count() > 0) {
            $score += 15;
        }

        // Skills (10)
        if ($this->skills()->count() > 0) {
            $score += 10;
        }

        // Extra (Languages / Projects / Certs) (5)
        if ($this->languages()->count() > 0 || $this->projects()->count() > 0 || $this->certifications()->count() > 0) {
            $score += 5;
        }

        return min(100, $score);
    }
}

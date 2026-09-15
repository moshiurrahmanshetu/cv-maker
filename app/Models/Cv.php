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
        'template_id',
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
        'template_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CvTemplate::class, 'template_id');
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

        // Title & basic info (15)
        if (!empty($this->title)) $score += 10;

        // Summary (15)
        if (!empty(trim($this->summary ?? ''))) $score += 15;

        // Personal Info (20)
        if ($this->personalInfo && !empty($this->personalInfo->full_name) && !empty($this->personalInfo->email)) {
            $score += 20;
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

        // Languages (5)
        if ($this->languages()->count() > 0) {
            $score += 5;
        }

        // Projects (5)
        if ($this->projects()->count() > 0) {
            $score += 5;
        }

        return min(100, $score);
    }

    /**
     * Get section checklist metadata for the builder navigation and sidebar checklist.
     */
    public function getSectionChecklist(): array
    {
        $hasPersonalInfo = $this->personalInfo && (!empty($this->personalInfo->full_name) || !empty($this->personalInfo->email));
        $hasSummary = !empty(trim($this->summary ?? ''));
        $expCount = $this->experiences()->count();
        $eduCount = $this->educations()->count();
        $skillCount = $this->skills()->count();
        $langCount = $this->languages()->count();
        $certCount = $this->certifications()->count();
        $projCount = $this->projects()->count();
        $awardCount = $this->awards()->count();
        $refCount = $this->references()->count();
        $customCount = $this->customSections()->count();

        return [
            'personal-info' => [
                'label' => 'Personal Information',
                'icon' => 'bi-person',
                'is_complete' => (bool)$hasPersonalInfo,
                'count' => $hasPersonalInfo ? 1 : 0,
            ],
            'summary' => [
                'label' => 'Profile Summary',
                'icon' => 'bi-card-text',
                'is_complete' => $hasSummary,
                'count' => $hasSummary ? 1 : 0,
            ],
            'experience' => [
                'label' => 'Work Experience',
                'icon' => 'bi-briefcase',
                'is_complete' => $expCount > 0,
                'count' => $expCount,
            ],
            'education' => [
                'label' => 'Education',
                'icon' => 'bi-mortarboard',
                'is_complete' => $eduCount > 0,
                'count' => $eduCount,
            ],
            'skills' => [
                'label' => 'Skills',
                'icon' => 'bi-tools',
                'is_complete' => $skillCount > 0,
                'count' => $skillCount,
            ],
            'languages' => [
                'label' => 'Languages',
                'icon' => 'bi-translate',
                'is_complete' => $langCount > 0,
                'count' => $langCount,
            ],
            'certifications' => [
                'label' => 'Certifications',
                'icon' => 'bi-patch-check',
                'is_complete' => $certCount > 0,
                'count' => $certCount,
            ],
            'projects' => [
                'label' => 'Projects',
                'icon' => 'bi-folder-check',
                'is_complete' => $projCount > 0,
                'count' => $projCount,
            ],
            'awards' => [
                'label' => 'Awards',
                'icon' => 'bi-trophy',
                'is_complete' => $awardCount > 0,
                'count' => $awardCount,
            ],
            'references' => [
                'label' => 'References',
                'icon' => 'bi-people',
                'is_complete' => $refCount > 0,
                'count' => $refCount,
            ],
            'custom' => [
                'label' => 'Custom Sections',
                'icon' => 'bi-plus-square',
                'is_complete' => $customCount > 0,
                'count' => $customCount,
            ],
        ];
    }
}

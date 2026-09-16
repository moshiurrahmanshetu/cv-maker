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

    protected $table = 'cvs';

    protected $fillable = [
        'user_id',
        'document_type_id',
        'template_id',
        'title',
        'slug',
        'summary',
        'status',
        'template_key',
        'primary_color',
        'font_family',
        'settings',
        'completion_percentage',
    ];

    protected $casts = [
        'completion_percentage' => 'integer',
        'template_id' => 'integer',
        'document_type_id' => 'integer',
        'settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CvTemplate::class, 'template_id');
    }

    public function personalInfo(): HasOne
    {
        return $this->hasOne(CvPersonalInfo::class, 'cv_id');
    }

    public function personalInformation(): HasOne
    {
        return $this->personalInfo();
    }

    public function letterDetail(): HasOne
    {
        return $this->hasOne(DocumentLetterDetail::class, 'cv_id');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(CvExperience::class, 'cv_id')->orderBy('sort_order')->orderBy('start_date', 'desc');
    }

    public function workExperiences(): HasMany
    {
        return $this->experiences();
    }

    public function educations(): HasMany
    {
        return $this->hasMany(CvEducation::class, 'cv_id')->orderBy('sort_order')->orderBy('start_date', 'desc');
    }

    public function education(): HasMany
    {
        return $this->educations();
    }

    public function skills(): HasMany
    {
        return $this->hasMany(CvSkill::class, 'cv_id')->orderBy('sort_order');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(CvLanguage::class, 'cv_id')->orderBy('sort_order');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(CvCertification::class, 'cv_id')->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(CvProject::class, 'cv_id')->orderBy('sort_order');
    }

    public function awards(): HasMany
    {
        return $this->hasMany(CvAward::class, 'cv_id')->orderBy('sort_order');
    }

    public function references(): HasMany
    {
        return $this->hasMany(CvReference::class, 'cv_id')->orderBy('sort_order');
    }

    public function customSections(): HasMany
    {
        return $this->hasMany(CvCustomSection::class, 'cv_id')->orderBy('sort_order');
    }

    public function aiUsageLogs(): HasMany
    {
        return $this->hasMany(AiUsageLog::class, 'cv_id');
    }

    public function atsAnalyses(): HasMany
    {
        return $this->hasMany(AtsAnalysis::class, 'cv_id')->latest();
    }

    public function latestAtsAnalysis(): HasOne
    {
        return $this->hasOne(AtsAnalysis::class, 'cv_id')->latestOfMany();
    }

    public function isDraft(): bool

    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isLetter(): bool
    {
        return $this->documentType?->isLetterBased() || in_array($this->documentType?->slug, ['cover-letter', 'motivation-letter']);
    }

    /**
     * Retrieve a specific customization setting with fallback.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Compute completion percentage based on filled sections according to document type.
     */
    public function calculateCompletion(): int
    {
        $score = 0;

        // Title is always present (10 pts)
        if (!empty($this->title)) $score += 10;

        if ($this->isLetter()) {
            // Letter-specific calculation
            if ($this->personalInfo && !empty($this->personalInfo->full_name) && !empty($this->personalInfo->email)) {
                $score += 25; // Sender info
            }

            if ($this->letterDetail) {
                if (!empty($this->letterDetail->recipient_name) || !empty($this->letterDetail->company_name)) {
                    $score += 20; // Recipient/Company
                }
                if (!empty($this->letterDetail->salutation)) {
                    $score += 10; // Greeting
                }
                if (!empty($this->letterDetail->body) || !empty($this->letterDetail->opening)) {
                    $score += 25; // Body/Motivation
                }
                if (!empty($this->letterDetail->closing) || !empty($this->letterDetail->sender_signature)) {
                    $score += 10; // Closing & Sign-off
                }
            }
        } else {
            // CV/Resume calculation
            if (!empty(trim($this->summary ?? ''))) $score += 15;

            if ($this->personalInfo && !empty($this->personalInfo->full_name) && !empty($this->personalInfo->email)) {
                $score += 20;
            }

            if ($this->experiences()->count() > 0) {
                $score += 20;
            }

            if ($this->educations()->count() > 0) {
                $score += 15;
            }

            if ($this->skills()->count() > 0) {
                $score += 10;
            }

            if ($this->languages()->count() > 0) {
                $score += 5;
            }

            if ($this->projects()->count() > 0) {
                $score += 5;
            }
        }

        return min(100, $score);
    }

    /**
     * Get section checklist metadata for the builder navigation and sidebar checklist.
     */
    public function getSectionChecklist(): array
    {
        if ($this->isLetter()) {
            $hasSender = $this->personalInfo && (!empty($this->personalInfo->full_name) || !empty($this->personalInfo->email));
            $hasRecipient = $this->letterDetail && (!empty($this->letterDetail->recipient_name) || !empty($this->letterDetail->company_name));
            $hasBody = $this->letterDetail && (!empty($this->letterDetail->body) || !empty($this->letterDetail->opening));
            $hasClosing = $this->letterDetail && (!empty($this->letterDetail->closing) || !empty($this->letterDetail->sender_signature));

            $isMotivation = $this->documentType?->slug === 'motivation-letter';

            return [
                'personal-info' => [
                    'label' => 'Sender Details',
                    'icon' => 'bi-person',
                    'is_complete' => (bool)$hasSender,
                    'count' => $hasSender ? 1 : 0,
                ],
                'letter-details' => [
                    'label' => $isMotivation ? 'Institution & Program' : 'Recipient & Organization',
                    'icon' => 'bi-building',
                    'is_complete' => (bool)$hasRecipient,
                    'count' => $hasRecipient ? 1 : 0,
                ],
                'letter-content' => [
                    'label' => $isMotivation ? 'Motivation & Purpose' : 'Letter Body & Content',
                    'icon' => 'bi-file-earmark-richtext',
                    'is_complete' => (bool)$hasBody,
                    'count' => $hasBody ? 1 : 0,
                ],
                'letter-closing' => [
                    'label' => 'Sign-off & Closing',
                    'icon' => 'bi-pen',
                    'is_complete' => (bool)$hasClosing,
                    'count' => $hasClosing ? 1 : 0,
                ],
            ];
        }

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

    /**
     * Check if this CV uses a premium template or premium document type.
     */
    public function isPremium(): bool
    {
        return (bool)($this->template?->is_premium ?? false);
    }

    /**
     * Check if user is authorized to download PDF for this document.
     */
    public function isDownloadableBy(User $user): bool
    {
        if ($this->user_id !== $user->id && !$user->isAdmin()) {
            return false;
        }

        if (!$this->isPremium()) {
            return true;
        }

        return $user->hasAccessToTemplate($this->template);
    }
}


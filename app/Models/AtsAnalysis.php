<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtsAnalysis extends Model
{
    use HasFactory;

    protected $table = 'ats_analyses';

    protected $fillable = [
        'cv_id',
        'user_id',
        'job_title',
        'job_description',
        'overall_score',
        'structure_score',
        'content_score',
        'skills_score',
        'completeness_score',
        'formatting_score',
        'match_score',
        'strengths',
        'issues',
        'suggestions',
        'matched_keywords',
        'missing_keywords',
        'metrics',
    ];

    protected $casts = [
        'overall_score' => 'integer',
        'structure_score' => 'integer',
        'content_score' => 'integer',
        'skills_score' => 'integer',
        'completeness_score' => 'integer',
        'formatting_score' => 'integer',
        'match_score' => 'integer',
        'strengths' => 'array',
        'issues' => 'array',
        'suggestions' => 'array',
        'matched_keywords' => 'array',
        'missing_keywords' => 'array',
        'metrics' => 'array',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine letter grade based on overall score.
     */
    public function getGrade(): string
    {
        return match (true) {
            $this->overall_score >= 85 => 'A',
            $this->overall_score >= 70 => 'B',
            $this->overall_score >= 50 => 'C',
            $this->overall_score >= 35 => 'D',
            default => 'F',
        };
    }

    /**
     * Color hex for score representation.
     */
    public function getColorHex(): string
    {
        return match (true) {
            $this->overall_score >= 85 => '#10b981',
            $this->overall_score >= 70 => '#0284c7',
            $this->overall_score >= 50 => '#f59e0b',
            default => '#ef4444',
        };
    }

    /**
     * Qualitative grade label.
     */
    public function getGradeLabel(): string
    {
        return match (true) {
            $this->overall_score >= 85 => 'Excellent ATS Ready',
            $this->overall_score >= 70 => 'Good ATS Compatibility',
            $this->overall_score >= 50 => 'Fair (Needs Optimization)',
            default => 'Needs Significant Improvement',
        };
    }

    /**
     * Job match score alias accessor.
     */
    public function getJobMatchScoreAttribute(): ?int
    {
        return $this->match_score;
    }

    /**
     * Job match score alias mutator.
     */
    public function setJobMatchScoreAttribute(?int $value): void
    {
        $this->attributes['match_score'] = $value;
    }
}

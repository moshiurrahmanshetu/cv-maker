<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CvPersonalInfo extends Model
{
    use HasFactory;

    protected $table = 'cv_personal_infos';

    protected $fillable = [
        'cv_id',
        'full_name',
        'job_title',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'website',
        'linkedin',
        'github',
        'other_url',
        'photo_path',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }

    /**
     * First name accessor.
     */
    public function getFirstNameAttribute(): ?string
    {
        if (empty($this->full_name)) {
            return null;
        }
        $parts = explode(' ', trim($this->full_name), 2);
        return $parts[0] ?? null;
    }

    /**
     * Last name accessor.
     */
    public function getLastNameAttribute(): ?string
    {
        if (empty($this->full_name)) {
            return null;
        }
        $parts = explode(' ', trim($this->full_name), 2);
        return $parts[1] ?? '';
    }

    /**
     * Bio alias pointing to parent CV summary.
     */
    public function getBioAttribute(): ?string
    {
        return $this->cv?->summary;
    }

    /**
     * Get accessible URL for profile photo.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }
}

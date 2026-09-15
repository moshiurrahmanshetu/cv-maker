<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'photo_path',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}

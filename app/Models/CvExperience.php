<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvExperience extends Model
{
    use HasFactory;

    protected $table = 'cv_experiences';

    protected $fillable = [
        'cv_id',
        'job_title',
        'employer',
        'city',
        'country',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}

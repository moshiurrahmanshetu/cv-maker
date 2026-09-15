<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvEducation extends Model
{
    use HasFactory;

    protected $table = 'cv_educations';

    protected $fillable = [
        'cv_id',
        'institution',
        'degree',
        'field_of_study',
        'city',
        'country',
        'start_date',
        'end_date',
        'is_current',
        'grade_or_gpa',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvCustomSection extends Model
{
    use HasFactory;

    protected $table = 'cv_custom_sections';

    protected $fillable = [
        'cv_id',
        'section_title',
        'content',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}

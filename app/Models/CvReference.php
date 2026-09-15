<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvReference extends Model
{
    use HasFactory;

    protected $table = 'cv_references';

    protected $fillable = [
        'cv_id',
        'full_name',
        'job_title',
        'company',
        'email',
        'phone',
        'relationship',
        'is_hidden',
        'sort_order',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvProject extends Model
{
    use HasFactory;

    protected $table = 'cv_projects';

    protected $fillable = [
        'cv_id',
        'title',
        'role',
        'project_url',
        'start_date',
        'end_date',
        'description',
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

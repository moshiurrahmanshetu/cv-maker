<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CvLanguage extends Model
{
    use HasFactory;

    protected $table = 'cv_languages';

    protected $fillable = [
        'cv_id',
        'language',
        'proficiency',
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

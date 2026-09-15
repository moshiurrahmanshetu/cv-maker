<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLetterDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'cv_id',
        'recipient_name',
        'recipient_title',
        'company_name',
        'company_address',
        'letter_date',
        'subject',
        'salutation',
        'opening',
        'body',
        'call_to_action',
        'closing',
        'sender_signature',
    ];

    /**
     * Parent Document / CV.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Cv::class, 'cv_id');
    }

    /**
     * CV relationship alias.
     */
    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class, 'cv_id');
    }
}

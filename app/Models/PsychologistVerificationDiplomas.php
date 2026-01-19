<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychologistVerificationDiplomas extends Model
{
    use HasFactory;

    protected $fillable = [
        'psychologist_verification_details_id',
        'file_url',
    ];

    public function verificationDetails(): BelongsTo
    {
        return $this->belongsTo(PsychologistVerificationDetails::class, 'psychologist_verification_details_id');
    }
}
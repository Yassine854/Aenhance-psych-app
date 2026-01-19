<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PsychologistVerificationDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'psychologist_profile_id',
        'rib',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'rib_file_url',
        'identity_type',
        'identity_number',
        'identity_file_url',
        'rejection_reason',
        'verification_status',
    ];

    protected $casts = [
        'verification_status' => 'string',
    ];

    public function psychologistProfile(): BelongsTo
    {
        return $this->belongsTo(PsychologistProfile::class);
    }

    public function diplomas(): HasMany
    {
        return $this->hasMany(PsychologistVerificationDiplomas::class);
    }
}
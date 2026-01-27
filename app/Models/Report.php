<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    protected $fillable = [
        'reporter_id',
        'reporter_type',
        'reported_id',
        'reported_type',
        'reason',
        'proof_image',
        'is_resolved',
        'resolved_at',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function reporter(): MorphTo
    {
        return $this->morphTo();
    }

    public function reported(): MorphTo
    {
        return $this->morphTo();
    }
}

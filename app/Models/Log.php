<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;

class Log extends Model
{
    use HasFactory;

    protected $table = 'logs';

    protected $fillable = [
        'actor_id',
        'actor_role',
        'action',
        'target_type',
        'target_id',
        'description',
    ];

    protected $casts = [
        'actor_id' => 'integer',
        'target_id' => 'integer',
    ];

    /**
     * Convenience helper to create a log entry.
     */
    public static function record(array $data)
    {
        return static::create($data);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'target_id');
    }
}

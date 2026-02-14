<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppFee extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'percentage',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];
}

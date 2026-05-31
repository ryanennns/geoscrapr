<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankedGamesScannedUserIds extends Model
{
    protected $guarded = [];

    protected $casts = [
        'user_ids' => 'array',
    ];
}

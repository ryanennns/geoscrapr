<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EloSnapshot extends Model
{
    use HasFactory;
    use HasUuids;

    const GAMEMODES = ['solo', 'team'];
    const TYPE_ELO_RANGE = 'elo_range';
    const TYPE_PERCENTILE = 'percentile';
    const TYPES = [self::TYPE_ELO_RANGE, self::TYPE_PERCENTILE];


    protected $guarded = [];
}

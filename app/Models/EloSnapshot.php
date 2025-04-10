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

    protected $guarded = [];
}

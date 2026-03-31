<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingChange extends Model
{
    use HasFactory;
    use HasUuids;

    public const TYPE_MOVING = 'moving';
    public const TYPE_NO_MOVE = 'no_move';
    public const TYPE_NMPZ = 'nmpz';
    public const TYPE_OVERALL = 'overall';

    protected $guarded = [];

    public function rateable()
    {
        return $this->morphTo();
    }
}

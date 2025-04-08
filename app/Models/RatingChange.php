<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingChange extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    public function rateable()
    {
        return $this->morphTo();
    }
}

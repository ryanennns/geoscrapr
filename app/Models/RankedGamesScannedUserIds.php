<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankedGamesScannedUserIds extends Model
{
    protected $guarded = [];

    protected $casts = [
        'user_ids' => 'array',
    ];

    public static function removeUserIds(array $userIds): void
    {
        $userIds = array_flip($userIds);

        static::query()->each(function (self $scannedUserIds) use ($userIds) {
            $scannedUserIds->update([
                'user_ids' => array_values(array_filter(
                    $scannedUserIds->user_ids,
                    fn (string $userId) => ! isset($userIds[$userId])
                )),
            ]);
        });
    }
}

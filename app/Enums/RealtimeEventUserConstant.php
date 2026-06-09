<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class RealtimeEventUserConstant extends Enum
{
    const STATUS_INVITING = 1;
    const STATUS_ACCEPTED = 2;

    public static function status()
    {
        return [
            self::STATUS_INVITING => 'Đang chờ lời mời',
            self::STATUS_ACCEPTED => 'Đã tham gia',
        ];
    }
}

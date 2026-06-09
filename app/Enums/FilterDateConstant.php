<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Carbon\Carbon;

final class FilterDateConstant extends Enum
{
    const ALL = -1;
    const TODAY = 1;
    const YESTERDAY = 2;
    const WEEK_AGO = 3;
    const MONTH_AGO = 4;
    const YEAR = 5;

    public static function getValueOption()
    {
        return [
          self::TODAY => Carbon::now()->startOfDay()->format('Y/m/d H:i'),
          self::YESTERDAY => Carbon::now()->startOfDay()->subDay()->format('Y/m/d H:i'),
          self::WEEK_AGO => Carbon::now()->startOfDay()->subWeek()->format('Y/m/d H:i'),
          self::MONTH_AGO => Carbon::now()->startOfDay()->subMonth()->format('Y/m/d H:i'),
          self::YEAR => Carbon::now()->startOfDay()->subYear()->format('Y/m/d H:i'),
        ];
    }
}

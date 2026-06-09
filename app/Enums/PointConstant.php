<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PointConstant extends Enum
{
    const POINT_PRACTICE = 1;
    const POINT_THEORETICAL = 2;
    const POINT_SUM = 3;

    const IS_ASSIGN = 1;
    const NOT_ASSIGN = 2;
}

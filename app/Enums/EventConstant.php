<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class EventConstant extends Enum
{
    const IS_CACHED_RANK = 1;
    const NOT_CACHED_RANK = 0;

    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
}

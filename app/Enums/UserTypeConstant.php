<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserTypeConstant extends Enum
{
    const ON = 'on';
    const OFF = 'off';

    const TYPE_ADMIN = 2;
    const TYPE_EDITOR = 3;
    const TYPE_TEACHER = 4;
    const TYPE_TEACHER_ADMIN = 111;
}

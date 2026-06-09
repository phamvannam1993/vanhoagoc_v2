<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserAccessModuleConstant extends Enum
{
    const PERMISSION_FULL = 1;
    const PERMISSION_VIEW = 2;
    const PERMISSION_EDIT = 3;
    const PERMISSION_DELETE = 4;

    const MODULE_TYPE_APP = 1;
    const MODULE_TYPE_BOOK = 2;
    const MODULE_TYPE_WEEK = 3;

    public static function moduleType()
    {
        return [
            self::MODULE_TYPE_APP => 'App',
            self::MODULE_TYPE_BOOK => 'Book',
            self::MODULE_TYPE_WEEK => 'Week',
        ];
    }
    public static function permission()
    {
        return [
            self::PERMISSION_FULL => 'Full',
            self::PERMISSION_VIEW => 'Xem',
            self::PERMISSION_EDIT => 'Sửa',
            self::PERMISSION_DELETE => 'Xóa',
        ];
    }
}

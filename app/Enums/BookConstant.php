<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class BookConstant extends Enum
{
    const ON = 'on';
    const OFF = 'off';

    const MEDIA_PATH = 'editor/img/book/';

    public static function fileAcceptType()
    {
        return ['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'];
    }
}

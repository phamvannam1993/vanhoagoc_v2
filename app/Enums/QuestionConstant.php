<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class QuestionConstant extends Enum
{
    const ON = 'on';
    const OFF = 'off';

    const DEFAULT_ORDER = 0;

    public static function type()
    {
        return [
            'choice' => 'Trắc nghiệm',
            'essay' => 'Tự luận',
            'button' => 'Nút bấm'
        ];
    }
}

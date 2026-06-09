<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class QuestionTemplateConstant extends Enum
{
    const ON = 'on';
    const OFF = 'off';

    public static function type()
    {
        return [
            'game_chon' => 'Game chọn đáp án đúng',
            'game_chon_sap_xep' => 'Game chọn và sắp xếp',
            'game_ghep_anh' => 'Game ghép ảnh',
            'game_keo' => 'Game kéo',
            'game_noi' => 'Game nối',
            'game_sap_xep' => 'Game sắp xếp',
            'game_dien_viet' => 'Game điền viết'
        ];
    }
}

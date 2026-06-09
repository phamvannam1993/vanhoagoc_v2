<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PracticeConstant extends Enum
{
    const ON = 'on';
    const OFF = 'off';

    const PREFIX_ID = 'luyentap';

    const IS_CONVERTED_PRACTICE_VIDEO_URL = 1;
    const IS_CONVERTED_PRACTICE_NOI_URL = 2;

    const MEDIA_PATH = 'editor/video/practice/';
}

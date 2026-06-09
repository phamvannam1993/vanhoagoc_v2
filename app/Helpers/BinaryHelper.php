<?php

namespace App\Helpers;

class BinaryHelper
{
    public static function compareBinary($input, $check)
    {
        return ($input & $check) == $check;
    }
}

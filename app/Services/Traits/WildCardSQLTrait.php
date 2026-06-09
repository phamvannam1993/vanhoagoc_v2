<?php

namespace App\Services\Traits;

trait WildCardSQLTrait
{
    // handle with search wildcard '%', '_' , '/'
    public function escapeLike($str) {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $str);
    }
}

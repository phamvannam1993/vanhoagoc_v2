<?php

namespace App\Repositories;

use App\Models\Novel;

class NovelRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Novel::class;
    }
}

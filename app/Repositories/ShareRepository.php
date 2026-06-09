<?php

namespace App\Repositories;

use App\Models\Share;

class ShareRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Share::class;
    }
}

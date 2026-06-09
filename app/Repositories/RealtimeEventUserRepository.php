<?php

namespace App\Repositories;

use App\Models\RealtimeEventUser;

class RealtimeEventUserRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return RealtimeEventUser::class;
    }
}

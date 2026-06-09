<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Item::class;
    }
}

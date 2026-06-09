<?php

namespace App\Repositories;

use App\Models\Inventory;

class InventoryRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Inventory::class;
    }
}

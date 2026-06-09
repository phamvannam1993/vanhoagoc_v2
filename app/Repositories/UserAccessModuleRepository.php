<?php

namespace App\Repositories;

use App\Models\UserAccessModule;

class UserAccessModuleRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserAccessModule::class;
    }

    public function getListByUserId($userId)
    {
        return $this->select()->with(['app.books', 'book.app', 'book.weeks', 'week.book.app'])->where('user_id', $userId)->get();
    }
}

<?php

namespace App\Repositories;

use App\Models\PracticeImage;

class PracticeImageRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return PracticeImage::class;
    }

    public function getList($params)
    {
        $query = $this->select();

        if(!empty($params['practice_id'])){
            $query->where('practice_id', $params['practice_id']);
        }

        return $query->get();
    }
}

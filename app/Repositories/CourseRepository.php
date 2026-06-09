<?php

namespace App\Repositories;

use App\Models\Course;

class CourseRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Course::class;
    }

    public function getCourseByApp($appId)
    {
        return $this->model->select('id', 'name')->get();
    }
}

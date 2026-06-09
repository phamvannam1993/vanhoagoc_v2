<?php

namespace App\Services;

use App\Repositories\CourseRepository;

class CourseService
{
    private $courseRepository;

    public function __construct(
        CourseRepository $courseRepository
    ) {
        $this->courseRepository = $courseRepository;
    }

    public function getList($data)
    {
        $list = $this->courseRepository->simplePaging([]);

        return $list;
    }

    public function getCourseByApp($appId)
    {
        return $this->courseRepository->getCourseByApp($appId);
    }
}

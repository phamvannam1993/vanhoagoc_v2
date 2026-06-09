<?php

namespace App\Services;

use App\Repositories\PracticeClassRepository;
use App\Services\Traits\ImageManagerTrait;
use Carbon\Carbon;

class PracticeClassService
{
    use ImageManagerTrait;

    private $practiceClassRepository;

    public function __construct(PracticeClassRepository $practiceClassRepository)
    {
        $this->practiceClassRepository = $practiceClassRepository;
    }

    public function store($data)
    {
        $studentId = $data['student_id'] ?? null;

        $filter = ['user_id' => $data['user_id'], 'class_id' => $data['class_id'], 'practice_id' => $data['practice_id']];
        if ($studentId) {
            $filter['student_id'] = $studentId;
        } else {
            // class-wide: only match records without a specific student
            $filter[] = ['student_id', null];
        }

        $record = $this->practiceClassRepository->findByFilter($filter);

        if (!$record) {
            $info['user_id']     = $data['user_id'];
            $info['student_id']  = $studentId;
            $info['book_id']     = $data['book_id'];
            $info['week_id']     = $data['week_id'];
            $info['class_id']    = $data['class_id'];
            $info['practice_id'] = $data['practice_id'];
            $info['created_at']  = Carbon::now();
            $info['updated_at']  = Carbon::now();

            if ($data['checkedTime']) {
                $info['from'] = $data['from'];
                $info['to']   = $data['to'];
            }

            $this->practiceClassRepository->create($info);

            return true;
        }

        return null;
    }

    public function deleteById($userId, $practiceId, $classId, $studentId = null)
    {
        $filter = ['user_id' => $userId, 'practice_id' => $practiceId, 'class_id' => $classId];
        if ($studentId) {
            $filter['student_id'] = $studentId;
        }
        return $this->practiceClassRepository->deleteByFilter($filter);
    }
}

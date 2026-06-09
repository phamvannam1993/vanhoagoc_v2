<?php

namespace App\Services;

use App\Repositories\UserProcessRepository;

class UserProcessService
{
    private $userProcessRepository;

    public function __construct(
        UserProcessRepository $userProcessRepository,
    ) {
        $this->userProcessRepository = $userProcessRepository;
    }

    public function save($data)
    {
        $this->userProcessRepository->updateOrCreate([
            'user_id' => $data['user_id'],
            'practice_id' => $data['practice_id'],
        ], [
            'user_id' => $data['user_id'],
            'practice_id' => $data['practice_id'],
            'book_name' => $data['book_name'],
            'week_number' => $data['week_number'],
            'lesson_number' => $data['lesson_number'],
        ]);
    }

    public function getList($data)
    {
        $list = $this->userProcessRepository->getList($data);

        $dataBook = [];
        $result = [];

        foreach ($list as $item) {
            $result[] = $item;
        }

        return $result;
    }
}

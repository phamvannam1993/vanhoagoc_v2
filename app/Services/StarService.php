<?php

namespace App\Services;

use App\Repositories\StarRepository;

class StarService
{
    private $starRepository;

    public function __construct(
        StarRepository $starRepository
    ) {
        $this->starRepository = $starRepository;
    }

    public function getStar($data)
    {
        $record = $this->starRepository->getDetail($data);

        if (empty($record->id)) {
            $dataSave = [
                'star' => 0,
                'user_id' => $data['user_id']
            ];

            $record = $this->starRepository->create($dataSave);
        }

        return $record;
    }

    public function save($data)
    {
        $this->starRepository->updateOrCreate([
            'user_id' => $data['user_id']
        ], $data);
    }
}

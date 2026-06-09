<?php

namespace App\Services;

use App\Enums\TargetConstant;
use App\Repositories\TargetRepository;
use App\Repositories\UserTargetItemRepository;

class TargetService
{
    private $targetRepository;
    private $userTargetItemRepository;

    public function __construct(
        TargetRepository $targetRepository,
        UserTargetItemRepository $userTargetItemRepository
    ) {
        $this->targetRepository = $targetRepository;
        $this->userTargetItemRepository = $userTargetItemRepository;
    }

    public function getDailyTarget()
    {
        $list = $this->targetRepository->findByFilter([
            'type' => TargetConstant::TYPE_DAILY
        ], [
            'with' => ['targetItems']
        ]);

        return $list;
    }

    public function getSightTarget()
    {
        $list = $this->targetRepository->findByFilter([
            'type' => TargetConstant::TYPE_SIGHT
        ], [
            'with' => ['targetItems']
        ]);

        return $list;
    }

    public function updateOrCreate($data)
    {
        $this->userTargetItemRepository->updateOrCreate([
            'user_id' => $data['user_id'],
            'target_type' => $data['target_type']
        ], $data);

        return [
            'success' => true,
            "message" => "Cập nhật thành công"
        ];
    }
}

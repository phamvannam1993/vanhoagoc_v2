<?php

namespace App\Services;

use App\Repositories\UserSettingRepository;

class UserSettingService
{
    private $userSettingRepository;

    public function __construct(
        UserSettingRepository $userSettingRepository
    ) {
        $this->userSettingRepository = $userSettingRepository;
    }

    public function createOrUpdate($data)
    {
        $this->userSettingRepository->updateOrCreate([
            'user_id' => $data['user_id']
        ], $data);
    }

    public function getDetail($data)
    {
        $record = $this->userSettingRepository->first(['user_id' => $data['user_id']]);

        if (empty($record)) {
            $record = [
                'is_sound_effect' => 0,
                'is_background_music' => 0,
                'is_auto_submit' => 0,
                'is_noti_exercise_reminder' => 0,
                'is_noti_encourage' => 0
            ];
        }

        return $record;
    }
}

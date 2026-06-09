<?php

namespace App\Services;

use App\Repositories\StreakRepository;

class StreakService
{
    private $streakRepository;

    public function __construct(
        StreakRepository $streakRepository
    ) {
        $this->streakRepository = $streakRepository;
    }

    public function getCurrentByUser($data)
    {
        $streak = $this->streakRepository->getCurrentStreakByUserId($data);

        return $streak;
    }

    public function getByUser($data)
    {
        $streak = $this->streakRepository->getByUser($data);

        return $streak;
    }

    public function update($data)
    {
        if (empty($data['user_id'])) {
            return [
                'success' => false,
                'message' => 'User ID không tồn tại'
            ];
        }

        $streak = $this->streakRepository->first(['user_id' => $data['user_id']]);

        if (empty($streak)) {
            $streak = $this->streakRepository->create([
                'user_id' => $data['user_id'],
                'start_date' => now()->toDateString(),
                'current_date' => now()->toDateString(),
                'day_count' => 1,
            ]);
        } else {
            if (!empty($data['day_count'])) {
                $streak->day_count = $data['day_count'];
            }
            $currentDate = \Carbon\Carbon::now()->toDateString();
            $streakDate = \Carbon\Carbon::parse($streak->current_date);
            $now = \Carbon\Carbon::now();

            $diffInDays = $streakDate->diffInDays($now->startOfDay(), false);

            //Tăng streak
            if ($diffInDays == 1) {
                $streak->day_count = $streak->day_count + 1;
                $streak->current_date = $currentDate;
                $streak->save();
            } elseif ($diffInDays == 0) {
            } elseif ($diffInDays >= 2) {
                //Reset streak
                $streak = $this->streakRepository->create([
                    'user_id' => $data['user_id'],
                    'start_date' => $currentDate,
                    'current_date' => $currentDate,
                    'day_count' => 1,
                ]);
            }
        }
        return $streak;
    }
}

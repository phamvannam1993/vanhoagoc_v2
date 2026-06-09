<?php

namespace App\Services\Hr;

use App\Enums\PointDayConstant;
use App\Repositories\PointDayRepository;
use App\Repositories\PointDetailRepository;
use App\Repositories\PointRepository;
use App\Repositories\PracticePointDayRepository;
use App\Repositories\UserInfoRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserTotalPointRepository;
use Carbon\Carbon;

class PointService
{
    private $practicePointDayRepository;
    private $pointRepository;
    private $pointDetailRepository;
    private $userTotalPointRepository;
    private $userInfoRepository;
    private $userRepository;
    private $pointDayRepository;

    public function __construct(
        PracticePointDayRepository $practicePointDayRepository,
        PointRepository $pointRepository,
        PointDetailRepository $pointDetailRepository,
        UserTotalPointRepository $userTotalPointRepository,
        UserInfoRepository $userInfoRepository,
        UserRepository $userRepository,
        PointDayRepository $pointDayRepository
    ) {
        $this->practicePointDayRepository = $practicePointDayRepository;
        $this->pointRepository = $pointRepository;
        $this->pointDetailRepository = $pointDetailRepository;
        $this->userTotalPointRepository = $userTotalPointRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->userRepository = $userRepository;
        $this->pointDayRepository = $pointDayRepository;
    }

    public function getListDay($today, $week, $userId, $total)
    {
        $datas = [];
        $strtotime = strtotime('monday this week');
        for ($i = 0; $i < 7; $i++) {
            $day = date('Y-m-d', $strtotime);
            $data = [
                'user_id' => $userId,
                'total' => 0,
                'day' => $day,
                'week' => $week,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            if ($day == $today) {
                $data['total'] =  $total;
            }
            array_push($datas, $data);
            $strtotime = strtotime('+1 day', $strtotime);
        }
        return $datas;
    }

    public function convertDataDetail($request, $id)
    {
        $data = [
            'question_id' => $request['question_id'],
            'answer' => isset($request['answer']) ? $request['answer'] : '',
            'star_count' =>  isset($request['star_count']) ? $request['star_count'] : '',
            'tem_playable_id' => isset($request['tem_playable_id']) ? $request['tem_playable_id'] : '',
            'answer_times' => isset($request['answer_times']) ? $request['answer_times'] : '',
            'playable' => isset($request['playable']) ? $request['playable'] : '',
            'screen_shot' => isset($request['screen_shot']) ? $request['screen_shot'] : '',
            'time' =>  $request['time'],
            'point_id' => $id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        return $data;
    }

    public function convertDataPoint($request)
    {
        $data = [
            'user_id' => $request['user_id'],
            'name' => $request['name'],
            'practice_id' => isset($request['practice_id']) ? $request['practice_id'] : '',
            'screen_shot' => isset($request['screen_shot']) ? $request['screen_shot'] : '',
            'namevideo' =>  isset($request['namevideo']) ? $request['namevideo'] : '',
            'book_id' =>  isset($request['book_id']) ? $request['book_id'] : '',
            'star_count' =>  $request['star_count'],
            'time' =>  $request['time'],
            'format' => isset($request['format']) ? $request['format'] : '',
            'type' =>  $request['type'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        return $data;
    }

    public function getListByType($data)
    {
        $list = $this->pointRepository->getListSummary($data);

        return $list;
    }

    public function getSummaryPoint($data)
    {
        $summary = $this->pointRepository->getSummary($data);

        return [
            'total_time' => empty($summary->total_time) ? 0 : $summary->total_time,
            'total_star_count' => empty($summary->total_star_count) ? 0 : $summary->total_star_count
        ];
    }

    public function getRank($data)
    {
        $list = $this->userTotalPointRepository->getList($data);

        return $list;
    }

    public function getTotalPoint($params)
    {
        $userId = $params['user_id'];
        $type = $params['type'];

        $total = $this->getTotal($userId, $type);
        if ($type == PointDayConstant::POINT_SIGHT) {

            $userInfo = $this->userInfoRepository->first(['user_id' => $userId]);
            $dataUserInfo = [
                'total_sight' => $total,
                'user_id' => $userId
            ];
            if (!empty($userInfo->id)) {
                if ($userInfo->total_record_sight < $total) {
                    $dataUserInfo['total_record_sight'] = $total;
                    $dataUserInfo['day_record_sight'] = date('Y-m-d H:i:s');
                }
                $this->userInfoRepository->updateByFilters(['id' => $userInfo->id], $dataUserInfo);
            } else {
                $dataUserInfo['total_record_sight'] = $total;
                $dataUserInfo['day_record_sight'] = date('Y-m-d H:i:s');
                $this->userInfoRepository->create($dataUserInfo);
            }
        }

        return $total;
    }

    public function getTotal($userId, $type)
    {
        $currentDay = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('yesterday'));

        $params = [];
        $pointDay = $this->pointDayRepository->first([
            'user_id' => $userId,
            'day' => $currentDay,
            'type' => $type,
        ]);

        if (!empty($pointDay->id)) {
            return $pointDay->total;
        } else {
            $data = [
                'user_id' => $userId,
                'type' => $type,
                'day' => $currentDay
            ];

            if ($type == PointDayConstant::POINT_SIGHT) {
                $data['total'] = 1;
                $yesterday = $this->pointDayRepository->first([
                    'user_id' => $userId,
                    'day' => $currentDay,
                    'type' => $type,
                ]);

                if (!empty($yesterday->id)) {
                    $data['total'] = $yesterday->total + 1;
                }
            } else {
                $data['total'] = PointDayConstant::TOTAL_FREE;
            }

            $result = $this->pointDayRepository->create($data);

            return $result->total;
        }
    }

    public function subTotalPoint($userId, $type)
    {
        $currentDay = date('Y-m-d');
        $params = [
            'user_id' => $userId,
            'day' => $currentDay,
            'type' => $type
        ];

        $pointDay = $this->pointDayRepository->first($params);

        if (!empty($pointDay->total)) {
            $dataUpdate = [
                'total' => $pointDay->total - 1
            ];
            return  $this->pointDayRepository->updateByFilters(['id' => $pointDay->id], $dataUpdate);
        }

        return true;
    }

    public function getPracticePointDay($data)
    {
        $userId = $data['user_id'];
        $day = date('Y-m-d');
        $week = date('W');

        $filters = [
            'user_id' => $userId,
            'week' => $week
        ];

        $result = $this->practicePointDayRepository->get($filters);

        if (count($result) == 0) {
            $dataSaves = $this->getListDay($day, $week, $userId, 0);

            $this->practicePointDayRepository->insert($dataSaves);
            $result = $this->practicePointDayRepository->get($filters);
        }

        return $result;
    }
}

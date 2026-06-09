<?php

namespace App\Services;

use App\Enums\EventConstant;
use App\Helpers\Helper;
use App\Repositories\EventPointRepository;
use App\Repositories\EventPracticeRepository;
use App\Repositories\EventRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function __construct(
        private EventRepository $eventRepository,
        private EventPracticeRepository $eventPracticeRepository,
        private EventPointRepository $eventPointRepository,
        private UserRepository $userRepository,
    ) {
        $this->_cacheRank();
    }

    public function getListPaginate($params = [])
    {
        return $this->eventRepository->getListPaginate($params);
    }

    public function store($params)
    {
        $event = $this->eventRepository->getModel()->create([
            'name' => $params['name'],
            'img_rule' => $params['img_rule'],
            'app_id' =>  $params['app_id'],
            'start_datetime' => $params['start_datetime'],
            'end_datetime' => $params['end_datetime'],
            'class_id' => data_get($params, 'class_id'),
            'number_of_times' => data_get($params, 'number_of_times'),
            'duration' => data_get($params, 'duration'),
            'create_id' => auth()->id(),
            'status' => EventConstant::STATUS_DRAFT,
        ]);

        foreach ($params['practice_ids'] as $practiceId) {
            $listEventPractice[] = [
                'practice_id' => $practiceId,
                'event_id' => $event->id
            ];
        }

        if (!empty($listEventPractice)) {
            $this->eventPracticeRepository->getModel()->insert($listEventPractice);
        }

    }

    public function jsonUpdate($params)
    {
        $event = $this->eventRepository->findOrFail($params['id']);

        $event->name = $params['name'];
        $event->img_rule = $params['img_rule'];
        $event->app_id = $params['app_id'];
        $event->start_datetime = $params['start_datetime'];
        $event->end_datetime = $params['end_datetime'];
        $event->class_id = data_get($params, 'class_id');
        $event->number_of_times = data_get($params, 'number_of_times');
        $event->duration = data_get($params, 'duration');

        $event->save();

        $this->eventPracticeRepository->where('event_id', $event->id)->delete();

        foreach ($params['practice_ids'] as $practiceId) {
            $listEventPractice[] = [
                'practice_id' => $practiceId,
                'event_id' => $event->id
            ];
        }

        if (!empty($listEventPractice)) {
            $this->eventPracticeRepository->getModel()->insert($listEventPractice);
        }
    }

    public function destroy($id)
    {
        return $this->eventRepository->getModel()->where('id', $id)->delete();
    }


    // For API
    public function getListApi($params)
    {
        $events = $this->eventRepository->getListApi($params);

        $data = [];

        foreach ($events as $event) {
            $practiceIds = [];
            foreach ($event['practices'] as $practice) {
                $week = $practice['week'];
                $book = $week['book'];
                $practiceIds[] = "{$book->bo_sach}.{$book->lop}.{$book->name}.quyen1.{$week->week_id}.{$practice->practice_id}";
            }
            $data[] = [
                'id' => $event->id,
                'name' => $event->name,
                'app_id' => $event->app_id,
                'img_rule' => $event->img_rule,
                'class_id' => $event->class_id,
                'practice_ids' => $practiceIds,
                'start_time'=> $event->start_datetime,
                'end_time'=> $event->end_datetime,
                'number_of_times'=> $event->number_of_times,
                'duration'=> $event->duration,
                'status' => $event->getEventStatus(),
                'label_status' => $event->getEventStatus(true),
            ];
        }

        return $data;
    }

     public function getDetailApi($id)
    {
        $event = $this->eventRepository->getDetailApi($id);

        if (empty($event)) {
            return [];
        }
        $practiceIds = [];
        foreach ($event['practices'] as $practice) {
            $week = $practice['week'];
            $book = $week['book'];
            $practiceIds[] = "{$book->bo_sach}.{$book->lop}.{$book->name}.quyen1.{$week->week_id}.{$practice->practice_id}";
        }
        return [
            'id' => $event->id,
            'name' => $event->name,
            'img_rule' => $event->img_rule,
            'class_id' => $event->class_id,
            'practice_ids' => $practiceIds,
            'start_time'=> $event->start_datetime,
            'end_time'=> $event->end_datetime,
            'number_of_times'=> $event->number_of_times,
            'duration'=> $event->duration,
            'status' => $event->getEventStatus(),
            'label_status' => $event->getEventStatus(true),
        ];
    }

    public function updatePoint($params)
    {
        return $this->eventPointRepository->updateOrCreate([
            'user_id' => $params['user_id'],
            'event_id' => $params['event_id'],
            'time_number' => $params['time_number'],
        ],
        [
            'user_id' => $params['user_id'],
            'event_id' => $params['event_id'],
            'star_count' => $params['star_count'],
            'star_total' => $params['star_total'],
            'time_number' => $params['time_number'],
            'time' => $params['time'],
        ]);
    }

    public function getRanking($params = [])
    {
        if (empty($params['event_id'])) {
            return [];
        }
        $paramUserId = data_get($params, 'user_id');
        unset($params['user_id']);
        $points = $this->eventPointRepository->getRanking($params);

        $data = [];
        $rank = 0;

        $bestResultByUserIds = [];
        foreach ($points as $value) {
            $user = $value->user;
            if (!empty($user)) {
                $userId = $value['user_id'];
                if (in_array($userId, $bestResultByUserIds)) {
                    continue;
                }

                $currentR = [
                    'star_count' => $value['star_count'],
                    'time' => $value['time'],
                    'time_number' => $value['time_number']
                ];

                if (empty($oldUserR) || $oldUserR != $currentR) {
                    $rank++;
                }

                $oldUserR = $currentR;

                $r = [
                    'event_id' => $value['event_id'],
                    'user_id' => $userId,
                    'class_id' => $value['class_id'],
                    'star_count' => $value['star_count'],
                    'star_total' => $value['star_total'],
                    'time' => $value['time'],
                    'time_number' => $value['time_number'],
                    'rank' => $rank,
                    'user' => [
                        'id' => $user->id,
                        'user_id_app' => $user->user_id_app,
                        'full_name' => $user->name,
                        'avatar' => '',
                    ]
                ];
                $bestResultByUserIds[] = $userId;
                if (!empty($paramUserId)) {
                    if($paramUserId == $userId) {
                        return [$r];
                    }
                }else {
                    $data[] = $r;
                }
            }
        }

        return $data;
    }

    public function getUserInfoByEvent($params)
    {
        $userId = data_get($params, 'user_id');

        if (empty($userId)) {
            return [];
        }
        $event = $this->eventRepository->findOrFail(data_get($params, 'event_id'));
        $user = $this->userRepository->findOrFail($userId);

        if (empty($event)) {
            return [];
        }

        $points = $this->eventPointRepository->getRanking($params, false, [], ['order_by' => 'time_number', 'order_type' => 'ASC']);

        return [
            'id' => $user->id,
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_name' => $event->name,
            'event_number_of_times' => $event->number_of_times,
            'event_remain_of_times' => $event->number_of_times - count($points),
            'event_duration' => $event->duration,
            'points' => $points->map(function($item){
                return [
                    'event_id' => $item['event_id'],
                    'user_id' => $item['user_id'],
                    'class_id' => $item['class_id'],
                    'star_count' => $item['star_count'],
                    'star_total' => $item['star_total'],
                    'time' => $item['time'],
                    'time_number' => $item['time_number'],
                ];
            })
        ];
    }
    //Cần phải dùng service để _cacheRank
    public function getHighestRank($params)
    {
        $event = $this->eventPointRepository->getHighestRankByUserId(data_get($params, 'user_id'));

        if (empty($event)) {
            return [];
        }

        return [
            'event_id' => $event->event_id,
            'highest_rank' => $event->rank,
        ];
    }

    private function _cacheRank()
    {
        try {
            $events = $this->eventRepository->getEventNotCachedRank();

            foreach ($events as $event) {
                try {
                    DB::beginTransaction();
                    $points = $this->eventPointRepository->getRanking(['event_id' => $event->id]);
                    $rank = 0;

                    $bestResultByUserIds = [];
                    foreach ($points as $point) {
                        $user = $point->user;
                        if (!empty($user)) {
                            $userId = $point['user_id'];
                            if (in_array($userId, $bestResultByUserIds)) {
                                continue;
                            }

                            $currentR = [
                                'star_count' => $point['star_count'],
                                'time' => $point['time'],
                                'time_number' => $point['time_number']
                            ];

                            if (empty($oldUserR) || $oldUserR != $currentR) {
                                $rank++;
                            }

                            $oldUserR = $currentR;
                            $bestResultByUserIds[] = $userId;
                            $point->rank = $rank;
                            $point->save();
                        }
                    }
                    $event->is_cached_rank = \App\Enums\EventConstant::IS_CACHED_RANK;
                    $event->save();
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Helper::logException($e);
                }
            }
        } catch (\Exception $e) {
            Helper::logException($e);
        }
    }
}

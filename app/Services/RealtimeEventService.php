<?php

namespace App\Services;

use App\Enums\RealtimeEventUserConstant;
use App\Repositories\RealtimeEventPointRepository;
use App\Repositories\RealtimeEventRepository;
use App\Repositories\RealtimeEventUserRepository;
use App\Repositories\UserRepository;

class RealtimeEventService
{
    public function __construct(
        private RealtimeEventRepository $realtimeEventRepository,
        private RealtimeEventPointRepository $realtimeEventPointRepository,
        private RealtimeEventUserRepository $realtimeEventUserRepository,
        private UserRepository $userRepository,
    ) {}

    public function getListPaginate($params = [])
    {
        return $this->realtimeEventRepository->getListPaginate($params);
    }

    public function store($params)
    {
        $event = $this->realtimeEventRepository->create([
            'source_id' => data_get($params, 'source_id'),
            'name' => data_get($params, 'name'),
        ]);

        return $event;
    }
    public function getListApi($params)
    {
        $events = $this->realtimeEventRepository->getListApi($params);
        return $events->map(function($event){
            return $this->__convertEventData($event);
        });
        return $events;
    }

    public function getDetailApi($id)
    {
        $event = $this->realtimeEventRepository->getDetailApi($id);
        if (empty($event)) {
            return [];
        }
        return $this->__convertEventData($event);
    }

    public function updatePoint($params)
    {
        $event = $this->realtimeEventRepository->findOrFail(data_get($params, 'realtime_event_id'));

        if (empty($event)) {
            throw new \Exception('Sự kiện không tồn tại!');
        }

        $user = $this->userRepository->findOrFail(data_get($params, 'user_id'));

        if (empty($user)) {
            throw new \Exception('Người dùng không tồn tại!');
        }

        $eventUser = $this->realtimeEventUserRepository->findByFilter([
            'realtime_event_id' => $event->id,
            'user_id' => $user->id
        ]);

        if (empty($eventUser) || $eventUser->status == \App\Enums\RealtimeEventUserConstant::STATUS_INVITING) {
            throw new \Exception('Người dùng chưa tham gia sự kiện này!');
        }

        return $this->realtimeEventPointRepository->updateOrCreate([
            'user_id' => $user->id,
            'realtime_event_id' => $event->id,
        ], [
            'star_count' => data_get($params, 'star_count'),
            'star_total' => data_get($params, 'star_total'),
            'time' => data_get($params, 'time'),
        ]);
    }

    public function getRanking($params = [])
    {
        if (empty($params['realtime_event_id'])) {
            throw new \Exception('Sự kiện không tồn tại!');
        }
        $paramUserId = data_get($params, 'user_id');
        unset($params['user_id']);
        $points = $this->realtimeEventPointRepository->getRanking($params);

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
                ];

                if (empty($oldUserR) || $oldUserR != $currentR) {
                    $rank++;
                }

                $oldUserR = $currentR;

                $r = [
                    'realtime_event_id' => $value['realtime_event_id'],
                    'user_id' => $userId,
                    'class_id' => $value['class_id'],
                    'star_count' => $value['star_count'],
                    'star_total' => $value['star_total'],
                    'time' => $value['time'],
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
                        return $r;
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
        $event = $this->realtimeEventRepository->findOrFail(data_get($params, 'realtime_event_id'));
        $user = $this->userRepository->findOrFail($userId);

        if (empty($event)) {
            return [];
        }

        $points = $this->realtimeEventPointRepository->getRanking($params, false, [], ['order_type' => 'ASC']);

        return [
            'id' => $user->id,
            'user_id' => $user->id,
            'realtime_event_id' => $event->id,
            'event_name' => $event->name,
            'event_duration' => $event->duration,
            'points' => $points->map(function($item){
                return [
                    'realtime_event_id' => $item['realtime_event_id'],
                    'user_id' => $item['user_id'],
                    'class_id' => $item['class_id'],
                    'star_count' => $item['star_count'],
                    'star_total' => $item['star_total'],
                    'time' => $item['time'],
                ];
            })
        ];
    }

    public function inviteOrAcceptInvite($params)
    {
        $user = $this->userRepository->findOrFail(data_get($params, 'user_id'));

        if (empty($user)) {
            throw new \Exception('User not found!');
        }

        $invitedUser = $this->userRepository->findOrFail(data_get($params, 'invited_user_id'));
        if (empty($invitedUser)) {
            throw new \Exception('Người được mời không tồn tại!');
        }

        $event = $this->realtimeEventRepository->findBySourceId(data_get($params, 'source_id'));

        if (empty($event)) {
            $event = $this->realtimeEventRepository->find(data_get($params, 'realtime_event_id'));
        }

        if (empty($event)) {
            $event = $this->realtimeEventRepository->create([
                'source_id' => data_get($params, 'source_id'),
                'name' => data_get($params, 'name'),
            ]);

            $this->realtimeEventUserRepository->create([
                'user_id' => $user->id,
                'realtime_event_id' => $event->id,
                'status' => RealtimeEventUserConstant::STATUS_INVITING,
            ]);
        }

        $this->realtimeEventUserRepository->updateOrCreate([
            'user_id' => $invitedUser->id,
            'realtime_event_id' => $event->id,
        ],
        [
            'user_id' => $invitedUser->id,
            'realtime_event_id' => $event->id,
            'status' => data_get($params, 'status', RealtimeEventUserConstant::STATUS_INVITING),
        ]);

        return $this->getDetailApi($event->id);
    }

    public function kickUser($params)
    {
        if (empty(data_get($params, 'user_id')) || empty(data_get($params, 'realtime_event_id'))) {
            throw new \Exception('Người dùng hoặc sự kiện không tồn tại!');
        }

        $this->realtimeEventUserRepository->deleteByFilter([
            'user_id' => $params['user_id'],
            'realtime_event_id' => $params['realtime_event_id'],
        ]);
        return true;
    }

    private function __convertEventData($event)
    {
        return [
            'id' => $event->id,
            'name' => $event->name,
            'users' => $event->realtimeEventUsers->map(function($item){
                $user = $item->user;
                if (empty($user)) {
                    return [];
                }
                return [
                    'id' => $user->id,
                    'user_id_app' => $user->user_id_app,
                    'full_name' => $user->name,
                    'avatar' => $user->avatar,
                    'status' => $item->status,
                    'status_label' => $item->getStatusLabel(),
                ];
            }),
        ];
    }
}

<?php

namespace App\Services;

use App\Enums\UserFollowConstant;
use App\Repositories\UserFollowRepository;
use App\Repositories\UserInfoRepository;
use App\Repositories\UserRepository;

class UserFollowService
{
    private $userRepository;
    private $userFollowRepository;
    private $userInfoRepository;

    public function __construct(
        UserRepository $userRepository,
        UserFollowRepository $userFollowRepository,
        UserInfoRepository $userInfoRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userFollowRepository = $userFollowRepository;
        $this->userInfoRepository = $userInfoRepository;
    }

    public function getListUsers($params)
    {
        $list = $this->userRepository->search($params);

        return $list;
    }

    public function returnFail($message)
    {
        return [
            'status' => false,
            'message' => $message
        ];
    }

    public function save($params)
    {
        $user = $this->userRepository->first([
            'id' => $params['user_follow_id']
        ]);

        if (!$user) {
            return $this->returnFail('user_follow_id không tồn tại');
        }

        $totalFollowUser = $this->userFollowRepository->getTotalByType($params['user_id'], UserFollowConstant::TYPE_FOLLOW); // Tổng đang follow
        $totalUserFollow = $this->userFollowRepository->getTotalByType($params['user_follow_id'], UserFollowConstant::TYPE_FOLLOWER); // Tổng người theo dõi

        $userFollow = $this->userFollowRepository->getUserFollow($params);

        if (($userFollow && $params['type'] == UserFollowConstant::TYPE_FOLLOW) || (!$userFollow && $params['type'] == UserFollowConstant::TYPE_FOLLOWER)) {
            return $this->returnFail('Dữ liệu đã được xử lý');
        }

        if ($params['type'] == UserFollowConstant::TYPE_FOLLOW) {
            $totalFollowUser = $totalFollowUser + 1;
            $totalUserFollow = $totalUserFollow + 1;
        } else {
            $totalFollowUser = $totalFollowUser - 1;
            $totalUserFollow = $totalUserFollow - 1;
        }

        if ($params['type'] == UserFollowConstant::TYPE_FOLLOW) {
            $this->userFollowRepository->create([
                'user_id' => $params['user_id'],
                'user_follow_id' => $params['user_follow_id']
            ]);
        } else {
            $this->userFollowRepository->deleteByFilter([
                'user_id' => $params['user_id'],
                'user_follow_id' => $params['user_follow_id']
            ]);
        }

        $this->userInfoRepository->updateOrCreate([
            'user_id' => $params['user_id']
        ], [
            'user_id' => $params['user_id'],
            'total_follow' => $totalFollowUser
        ]);

        $this->userInfoRepository->updateOrCreate([
            'user_id' => $params['user_follow_id']
        ], [
            'user_id' => $params['user_follow_id'],
            'total_user_follow' => $totalUserFollow
        ]);
    }

    public function getList($data)
    {
        if ($data['type'] == UserFollowConstant::TYPE_FOLLOW) {
            $params = ['user_follow_id' => $data['user_id']];
        } else {
            $params = ['user_id' => $data['user_id']];
        }

        $list = $this->userFollowRepository->getListUserFollow($params);

        return $list;
    }

    public function getUserFollowInfo($data)
    {
        if (empty(data_get($data, 'user_profile_id')) || empty(data_get($data, 'user_id'))) {
            return [
                'following' => 0,
                'followers' => 0,
                'is_following' => false
            ];
        }
        $following = $this->userFollowRepository->countFollowingByUserId($data['user_profile_id']);
        $followers = $this->userFollowRepository->countFollowersByUserId($data['user_profile_id']);

        return [
            'following' => $following,
            'followers' => $followers,
            'is_following' => $this->userFollowRepository->isFollowing(data_get($data, 'user_profile_id'), data_get($data, 'user_id'))
        ];
    }
}

<?php

namespace App\Services;

use App\Enums\UserConstant;
use App\Enums\UserTypeConstant;
use App\Repositories\AppRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserTypeRepository;
use Illuminate\Support\Facades\Hash;

class UserTypeService
{
    private $userTypeRepository;
    private $userRepository;
    private $roleRepository;
    private $appRepository;

    public function __construct(
        UserTypeRepository $userTypeRepository,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        AppRepository $appRepository
    ) {
        $this->userTypeRepository = $userTypeRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->appRepository = $appRepository;
    }

    public function getList()
    {
        $list = $this->userTypeRepository->getList();

        return $list;
    }

    public function getListByType($params)
    {
        $list = $this->userRepository->getListByType($params['type_id']);

        $appIds = $list->pluck('userApp')->flatten()
            ->map(fn($role) => ltrim($role->permission, 'p_'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $apps = \App\Models\App::whereIn('id', $appIds)->pluck('name', 'id');

        foreach ($list as $user) {
            $user->app_names = $user->userApp
                ->map(fn($role) => $apps[ltrim($role->permission, 'p_')] ?? null)
                ->filter()
                ->values()
                ->all();
        }

        return $list;
    }

    public function getListAppByUser($userId)
    {
        $list = $this->appRepository->getListByUser($userId);

        return $list;
    }

   public function assignRole(array $params): void
    {
        $userId = $params['user_id'] ?? null;
        $appId = $params['app_id'] ?? null;
        $typeId = $params['type_id'] ?? null;
        $checked = !empty($params['checked']);

        if (empty($userId) || empty($appId) || empty($typeId)) {
            return;
        }

        // Xóa cả 2 format (p_id và plain id) để tránh duplicate
        if ($checked) {
            $this->roleRepository->deleteByFilter(['user_id' => $userId, 'permission' => 'p_'.$appId, 'user_type_id' => $typeId]);
            $this->roleRepository->deleteByFilter(['user_id' => $userId, 'permission' => (string)$appId, 'user_type_id' => $typeId]);
            return;
        }

        // Kiểm tra đã có quyền chưa (cả 2 format)
        $exists = $this->roleRepository->first(['user_id' => $userId, 'user_type_id' => $typeId, 'permission' => 'p_'.$appId])
            ?? $this->roleRepository->first(['user_id' => $userId, 'user_type_id' => $typeId, 'permission' => (string)$appId]);

        if (empty($exists)) {
            $this->roleRepository->create([
                'user_id' => $userId,
                'permission' => (string)$appId,
                'user_type_id' => $typeId,
            ]);
        }
    }

    public function getAvailableType()
    {
        $list = $this->userTypeRepository->get([
            'status' => UserTypeConstant::ON
        ]);

        return $list;
    }

    public function createMember($data)
    {
        $oldRecord = $this->userRepository->first([
            'email' => $data['email']
        ]);

        if (!empty($oldRecord->id)) {
            return [
                'status' => false,
                'messages' => [
                    'email' => 'Email đã tồn tại.'
                ]
            ];
        } else {
            $record = $this->userRepository->create([
                'email' => $data['email'],
                'name' => $data['name'] ?? null,
                'user_type_id' => $data['type_id'],
                'password' => Hash::make($data['password']),
                'status' => UserConstant::ON,
                'tel' => $data['phone_number'] ?? null
            ]);

            return [
                'status' => true,
                'data' => [
                    'user' => $record
                ]
            ];
        }
    }

    public function updateMember($data)
    {
        $oldRecord = $this->userRepository->first([
            'id' => $data['id']
        ]);

        if (empty($oldRecord->id)) {
            return [
                'status' => false,
                'messages' => [
                    'email' => 'Không tìm thấy user'
                ]
            ];
        } else {
            $existOtherUser = $this->userRepository->getUserByEmail($data['id'], $data['email']);

            if ($existOtherUser) {
                return [
                    'status' => false,
                    'messages' => [
                        'email' => 'Email đã tồn tại.'
                    ]
                ];
            }

            $dataUpdate = [
                'email' => $data['email'],
                'name' => $data['name'],
                'tel' => $data['phone_number']
            ];

            if (!empty($data['img'])) {
                $dataUpdate['img'] = $data['img'];
            }

            if (!empty($data['type_id'])) {
                $dataUpdate['user_type_id'] = $data['type_id'];
            }

            if (!empty($data['password'])) {
                $dataUpdate['password'] = Hash::make($data['password']);
            }

            $oldRecord->fill($dataUpdate)->save();

            return [
                'status' => true
            ];
        }
    }

    public function delete($id)
    {
        $oldRecord = $this->userRepository->first(['id' => $id]);

        if (!empty($oldRecord->id)) {
            $typeId = $oldRecord->user_type_id;

            $this->userRepository->deleteByFilter([
                'id' => $id
            ]);

            return [
                'status' => true,
                'type_id' => $typeId
            ];
        }


        return [
            'status' => false,
            'messages' => [
                'id' => 'User không tồn tại!'
            ]
        ];
    }
}

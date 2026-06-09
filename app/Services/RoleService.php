<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use App\Services\Traits\ImageManagerTrait;

class RoleService
{
    use ImageManagerTrait;

    private $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function assignRoleUser($data)
    {
        return $this->roleRepository->updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'permission' => $data['app_id'],
            ],
            [
                'user_type_id' => $data['type_id'],
            ]
        );
    }

    /**
     * Xóa toàn bộ role cũ của user rồi gán role mới.
     * Dùng khi UPDATE teacher/director để tránh:
     * - Tích lũy nhiều role cho các app cũ
     * - Overwrite nhầm role của user khác nếu DB có unique trên permission
     */
    public function reassignRoleUser($data)
    {
        // Xóa tất cả role hiện tại của user này trước
        $this->roleRepository->deleteByFilter([
            'user_id' => $data['user_id'],
        ]);

        // Tạo role mới
        return $this->roleRepository->updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'permission' => $data['app_id'],
            ],
            [
                'user_type_id' => $data['type_id'],
            ]
        );
    }

    public function deleteRole($data)
    {
        $this->roleRepository->deleteByFilter([
            'user_id' => $data['user_id'],
            'permission' => $data['app_id']
        ]);
    }

    public function deleteRoleDirector($userId)
    {
        $this->roleRepository->deleteByFilter([
           'user_id' => $userId
        ]);
    }

    public function getAppByUser($userId)
    {
        return $this->roleRepository->getAppByUser($userId);
    }
}

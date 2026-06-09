<?php

namespace App\Services;

use App\Repositories\BadgeRepository;
use App\Repositories\UserBadgeRepository;
use App\Services\Traits\ImageManagerTrait;

class BadgeService
{
    use ImageManagerTrait;

    private $badgeRepository;
    private $userBadgeRepository;

    public function __construct(
        BadgeRepository $badgeRepository,
        UserBadgeRepository $userBadgeRepository
    ) {
        $this->badgeRepository = $badgeRepository;
        $this->userBadgeRepository = $userBadgeRepository;
    }

    public function getBadge()
    {
        $list = $this->badgeRepository->all();

        foreach ($list as $item) {
            $item->icon_url = !empty($item->icon_url) ? config('common.aws.cloud_front_domain') . '/images/badges/' . $item->icon_url : '';
        }

        return $list;
    }

    public function getDetail($id)
    {
        $badge = $this->badgeRepository->first([
            'id' => $id
        ]);

        $badge->icon_url = !empty($badge->icon_url) ? config('common.aws.cloud_front_domain') . '/images/badges/' . $badge->icon_url : '';

        return $badge;
    }

    public function save($params)
    {
        $badge = $this->badgeRepository->first(['id' => $params['badge_id']]);

        if (empty($badge->id)) {
            return ['success' => false, 'message' => 'Không tồn tại huy hiệu'];
        }

        $this->userBadgeRepository->create([
            'user_id' => $params['user_id'],
            'badge_id' => $params['badge_id'],
            'date_earned' => date('Y-m-d h:i:s')
        ]);

        return [
            'success' => true,
            'message' => 'Cập nhật thành công'
        ];
    }

    public function getUserBadge($params)
    {
        $userBadges = $this->userBadgeRepository->get([
            'user_id' => $params['user_id']
        ], [
            'with' => ['badge']
        ]);

        foreach ($userBadges as $userBadge) {
            if (!empty($userBadge->badge->icon_url)) {
                $userBadge->badge->icon_url = config('common.aws.cloud_front_domain') . '/images/badges/' . $userBadge->badge->icon_url;
            }
        }

        return $userBadges;
    }
}

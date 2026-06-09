<?php

namespace App\Services;

use App\Repositories\LogRepository;
use App\Repositories\ShareRepository;

class ShareService
{
    private $shareRepository;
    private $logRepository;

    public function __construct(
        ShareRepository $shareRepository,
        LogRepository $logRepository
    ) {
        $this->shareRepository = $shareRepository;
        $this->logRepository = $logRepository;
    }

    public function save($request)
    {
        if (empty($request['type'])) {
            return ['success' => false, 'message' => 'Trường type không được để trống'];
        }

        if (empty($request['target_data'])) {
            return ['success' => false, 'message' => 'Trường target_data không được để trống'];
        }

        $dataShare = [
            'user_id' => $request['user_id'],
            'click_count' => 0,
            'type' => $request['type'],
            'image_url' => isset($request['image_url']) ? $request['image_url'] : '',
            'created_at' => date('Y-m-d H:i:s'),
            'target_data' => json_encode($request['target_data'])
        ];

        $shareRecord = $this->shareRepository->create($dataShare);

        $shareBaseUrl = env('SHARE_BASE_URL');
        $url = $shareBaseUrl . '/share/' . $request->type . '/' . $shareRecord->id;
        $this->shareRepository->updateByFilters(['id' => $shareRecord->id], ['url' => $url]);

        $dataRes = [
            'id' => $shareRecord->id,
            'url' => $url,
            'created_at' => $dataShare['created_at'],
            'click_count' => 0
        ];

        $log = [
            'share_id' => $shareRecord->id,
            'user_id' => $request['user_id'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];

        $this->logRepository->create($log);

        return [
            'success' => true,
            'data' => $dataRes
        ];
    }

    public function getShare($data)
    {
        $share = $this->shareRepository->first(['id' => $data['id']]);

        if (empty($share->id)) {
            return ['success' => false, 'message' => 'Không tồn tại chia sẻ'];
        }

        $share->target_data = json_decode($share->target_data);

        return ['success' => true, 'data' => $share];
    }

    public function getUserShare($data)
    {
        $shares = $this->shareRepository->get(['user_id' => $data['user_id']]);

        return ['success' => true, 'data' => $shares];
    }

    public function postClickCount($data)
    {
        $share = $this->shareRepository->first(['id' => $data['share_id']]);

        if (empty($share->id)) {
            return ['success' => false, 'message' => 'không tồn tại chia sẻ'];
        }

        $clickCount = $share->click_count + 1;

        $log = [
            'share_id' => $share->id,
            'user_id' => $data['user_id'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];

        $this->shareRepository->updateByFilters(['id' => $share->id], ['click_count' => $clickCount]);
        $this->logRepository->create($log);

        return ['success' => true, 'message' => 'Cập nhật thành công'];
    }
}

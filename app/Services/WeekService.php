<?php

namespace App\Services;

use App\Enums\WeekConstant;
use App\Helpers\Helper;
use App\Repositories\BookRepository;
use App\Repositories\PracticeRepository;
use App\Repositories\WeekRepository;
use App\Services\Traits\ImageManagerTrait;
use App\Services\UserAccessModuleService;

class WeekService
{
    use ImageManagerTrait;

    private $weekRepository;
    private $bookRepository;
    private $practiceRepository;

    public function __construct(
        PracticeRepository $practiceRepository,
        WeekRepository $weekRepository,
        BookRepository $bookRepository
    ) {
        $this->weekRepository = $weekRepository;
        $this->bookRepository = $bookRepository;
        $this->practiceRepository = $practiceRepository;
    }

    /**
     * Kiểm tra user hiện tại có quyền thao tác trên book_id không.
     * Admin luôn được phép. Teacher kiểm tra qua danh sách book được phân quyền.
     */
    private function canAccessBook($bookId): bool
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return true;
        }
        [$allowedBookIds] = app(UserAccessModuleService::class)->getBookIds();
        return in_array($bookId, $allowedBookIds);
    }

    public function getList($data)
    {
        $weekIds = app(UserAccessModuleService::class)->getWeekIds(data_get($data, 'book_id'));

        $list = $this->weekRepository->getListByFilters($data, $weekIds);

        foreach ($list as $item) {
            $getPractice = $this->practiceRepository->get(['week_id' => $item->id]);
            $item->has_practices = count($getPractice) ? true : false;
            $item->count_practice = count($getPractice);
            $item->img = $item->img ? Helper::getCloudFront($item->img) : null;
        }

        return $list;
    }

    public function getNumberOfWeekArray($numberWeek = 0)
    {
        $result = [];
        $start = $numberWeek + 1;
        $end = $numberWeek + 20;

        for ($i = $start; $i <= $end; $i++) {
            $result[] = [
                'value' => $i,
                'label' => 'Tuần ' . $i
            ];
        }

        return $result;
    }

    public function store($data)
    {
        if (empty($data['book_id'])) {
            return [
                'status' => false,
                'messages' => ['book_id' => 'Thiếu book_id vui lòng thêm lại.']
            ];
        }

        if (!$this->canAccessBook($data['book_id'])) {
            return [
                'status' => false,
                'messages' => ['error' => 'Bạn không có quyền thao tác trên môn học này.']
            ];
        }

        $oldRecord = $this->weekRepository->first([
            'numberWeek' => $data['numberWeek'],
            'book_id' => $data['book_id']
        ]);

        if (!empty($oldRecord->id)) {
            return [
                'status' => false,
                'messages' => [
                    'numberWeek' => 'Tuần đã tồn tại.'
                ]
            ];
        }


        $user = auth()->user();
        $this->weekRepository->create([
            'name' => $data['name'],
            'user_id' => $user->id,
            'book_id' => $data['book_id'],
            'week_id' => WeekConstant::PREFIX_ID . $data['numberWeek'],
            'numberWeek' => $data['numberWeek'],
            'status' => WeekConstant::ON,
            'img' => $data['img'],
            'sort_number' => $data['sort_number']
        ]);

        return [
            'status' => true
        ];
    }

    public function getDetail($id)
    {
        $record = $this->weekRepository->first([
            'id' => $id
        ]);

        return $record;
    }

    public function update($data)
    {
        $record = $this->weekRepository->first([
            'id' => $data['id']
        ]);

        if (empty($record->id)) {
            return [
                'status' => false,
                'messages' => ['error' => 'ID không tồn tại.']
            ];
        }

        if (!$this->canAccessBook($record->book_id)) {
            return [
                'status' => false,
                'messages' => ['error' => 'Bạn không có quyền thao tác trên tuần này.']
            ];
        }

        $checkExist = $this->weekRepository->findByFilter([
            'numberWeek' => $data['numberWeek'],
            'book_id' => $record->book_id
        ], [
            'except' => [
                'id' => $record->id
            ]
        ]);

        if (!empty($checkExist->id)) {
            return [
                'status' => false,
                'messages' => [
                    'numberWeek' => 'Tuần đã tồn tại.'
                ]
            ];
        }

        if (!empty($data['delete_file'])) {
            $record->img = null;
        }

        $record->numberWeek = $data['numberWeek'];
        $record->week_id = WeekConstant::PREFIX_ID . $data['numberWeek'];
        $record->name = $data['name'];
        $record->img = empty($data['img']) ? null : $data['img'];

        $record->save();

        return [
            'status' => true,
            'book_id' => $record->book_id
        ];
    }

    public function delete($id)
    {
        $record = $this->weekRepository->first(['id' => $id]);

        if (!$this->canAccessBook($record->book_id ?? null)) {
            return [
                'status' => false,
                'messages' => ['error' => 'Bạn không có quyền xóa tuần này.']
            ];
        }

        $check = $this->practiceRepository->findByFilter([
            'week_id' => $id
        ]);

        if (!empty($check->id)) {
            return [
                'status' => false,
                'messages' => ['delete' => 'Lỗi,vui lòng xóa luyện tập trước']
            ];
        }

        // delete media resource
        if (!empty($record->img)) {
            $this->deleteFile(WeekConstant::MEDIA_PATH, $record->img);
        }

        $this->weekRepository->deleteByFilter([
            'id' => $id
        ]);

        return [
            'status' => true
        ];
    }

    public function updateVisible($data)
    {
        $record = $this->weekRepository->first(['id' => $data['id']]);
        if (!$this->canAccessBook($record->book_id ?? null)) {
            return;
        }

        $this->weekRepository->updateByFilters([
            'id' => $data['id']
        ], [
            'status' => $data['status'] == 'on' ? 'on' : 'off'
        ]);
    }

    public function getListByBook($bookId)
    {
        return $this->weekRepository->get(['book_id' => $bookId]);
    }

    public function getPrevById($recordTo)
    {
        return $this->weekRepository->getPrevById($recordTo);
    }
    public function getNextById($recordTo)
    {
        return $this->weekRepository->getNextById($recordTo);
    }

    public function updateNextPosition($recordFrom, $recordTo, $nextToId)
    {
        if (!$this->canAccessBook($recordFrom->book_id ?? null)) {
            return;
        }
        if (!$nextToId) {
            $this->weekRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => microtime(true) * 10000
            ]);
        } else {
            $this->weekRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $nextToId->sort_number) / 2
            ]);
        }
    }
    public function updatePrevPosition($recordFrom, $recordTo, $prevToId)
    {
        if (!$this->canAccessBook($recordFrom->book_id ?? null)) {
            return;
        }
        if (!$prevToId) {
            $this->weekRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => $recordTo->sort_number - 1
            ]);
        } else {
            $this->weekRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $prevToId->sort_number) / 2
            ]);
        }
    }
}

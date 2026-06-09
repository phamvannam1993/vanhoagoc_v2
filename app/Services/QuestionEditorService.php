<?php

namespace App\Services;

use App\Enums\PracticeConstant;
use App\Repositories\PracticeRepository;
use App\Repositories\QuestionEditorRepository;
use App\Services\Traits\ImageManagerTrait;

class QuestionEditorService
{
    use ImageManagerTrait;

    private $practiceRepository;
    private $questionEditorRepository;

    public function __construct(
        QuestionEditorRepository $questionEditorRepository,
        PracticeRepository $practiceRepository
    ) {
        $this->practiceRepository = $practiceRepository;
        $this->questionEditorRepository = $questionEditorRepository;
    }

    public function getList($data)
    {
        $list = $this->questionEditorRepository->getListByFilters($data);

        return $list;
    }

    public function getAll()
    {
        $list = $this->practiceRepository->all();

        return $list;
    }

    public function store($data)
    {
        if (empty($data['book_id']) || empty($data['week_id']) || empty($data['name'])) {
            return false;
        }

        $sortOrder = $this->practiceRepository->getSortOrder($data['book_id'], $data['week_id']);

        $user = auth()->user();
        $this->practiceRepository->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'sort_order' => $sortOrder,
            'taptrung' => $data['taptrung'],
            'week_id' => $data['week_id'],
            'book_id' => $data['book_id'],
            'status' => PracticeConstant::ON,
            'practice_id' => PracticeConstant::PREFIX_ID . $data['numberPractice'],
            'numberPractice' => $data['numberPractice']
        ]);

        return true;
    }

    public function getDetail($id)
    {
        return $this->practiceRepository->findByFilter([
            'id' => $id
        ]);
    }

    public function getDetailQuestionEditor($id)
    {
        return $this->questionEditorRepository->findOrFail($id);
    }

    public function update($data)
    {
        $record = $this->practiceRepository->findByFilter([
            'id' => $data['id']
        ]);

        if (empty($record->id)) {
            return [
                'status' => false,
                'messages' => [
                    'id' => 'Bài học không tồn tại.'
                ]
            ];
        } else {
            $record->name = $data['name'];
            $record->book_id = $data['book_id'];
            $record->week_id = $data['week_id'];
            $record->taptrung = $data['taptrung'];
            $record->numberPractice = $data['numberPractice'];
            $record->practice_id = PracticeConstant::PREFIX_ID . $data['numberPractice'];

            $record->save();

            return [
                'status' => true
            ];
        }
    }

    public function delete($id)
    {
        $this->questionEditorRepository->deleteByFilter([
            'id' => $id
        ]);

        return [
            'status' => true
        ];
    }

    public function updateVisible($data)
    {
        $this->questionEditorRepository->updateByFilters([
            'id' => $data['id']
        ], [
            'status' => $data['status'] == 'on' ? 'on' : 'off'
        ]);
    }

    public function getPrevById($recordTo)
    {
        return $this->questionEditorRepository->getPrevById($recordTo);
    }
    public function getNextById($recordTo)
    {
        return $this->questionEditorRepository->getNextById($recordTo);
    }

    public function updateNextPosition($recordFrom, $recordTo, $nextToId)
    {
        if (!$nextToId) {
            $this->questionEditorRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => microtime(true) * 10000
            ]);
        } else {
            $this->questionEditorRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $nextToId->sort_number) / 2
            ]);
        }
    }
    public function updatePrevPosition($recordFrom, $recordTo, $prevToId)
    {
        if (!$prevToId) {
            $this->questionEditorRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => $recordTo->sort_number - 1
            ]);
        } else {
            $this->questionEditorRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $prevToId->sort_number) / 2
            ]);
        }
    }

    public function dragPosition($fromId, $toId)
    {
        $recordFrom =$this->questionEditorRepository->findOrFail($fromId);
        $recordTo =$this->questionEditorRepository->findOrFail($toId);
        $sortNumber = $recordTo->sort_number;

        if ($recordFrom->sort_number > $recordTo->sort_number) {
            $middleRecords = $this->questionEditorRepository->getMiddleRecords($recordTo, $recordFrom);
            $recordFrom->sort_number = $sortNumber;
            $recordFrom->save();
            $sortNumber += 1000;
            $recordTo->sort_number = $sortNumber;
            $recordTo->save();
            foreach ($middleRecords as $record) {
                $sortNumber += 1000;
                $record->sort_number = $sortNumber;
                $record->save();
            }
        }else {
            $middleRecords = $this->questionEditorRepository->getMiddleRecords($recordFrom, $recordTo);
            $recordFrom->sort_number = $sortNumber;
            $recordFrom->save();
            $sortNumber -= 1000;
            $recordTo->sort_number = $sortNumber;
            $recordTo->save();
            foreach ($middleRecords as $record) {
                $sortNumber -= 1000;
                $record->sort_number = $sortNumber;
                $record->save();
            }
        }
    }

    public function resetPosition($params)
    {
        $listQuestionEditors = $this->questionEditorRepository->getListByFilters([
            'book_id' => $params['book_id'],
            'week_id' => $params['week_id'],
            'practice_id' => $params['practice_id'],
            'sort_by' => 'sort_number',
            'sort_type' => 'ASC'
        ], [], true);
        $sortNumber = microtime(true) * 10000;
        foreach ($listQuestionEditors as $questionEditor) {
            $sortNumber += 1000;
            $questionEditor->sort_number = $sortNumber;
            $questionEditor->save();
        }
    }
}

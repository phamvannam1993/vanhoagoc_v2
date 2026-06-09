<?php

namespace App\Services;

use App\Enums\PracticeConstant;
use App\Enums\QuestionConstant;
use App\Repositories\QuestionRepository;

class QuestionService
{
    private $questionRepository;

    public function __construct(
        QuestionRepository $questionRepository
    ) {
        $this->questionRepository = $questionRepository;
    }

    public function getList($data)
    {
        $list = $this->questionRepository->getListByFilters($data);

        $list->through(function ($value) {
            $answer = $value->answers->where('status', 'on')->first();
            $value->has_answer = empty($answer->id) ? 0 : 1;

            return $value;
        });

        return $list;
    }

    public function store($data)
    {
        $typeArray = QuestionConstant::type();
        $user = auth()->user();

        $record = $this->questionRepository->create([
            'name' => $data['name'],
            'question_type' => $data['question_type'],
            'type' => $typeArray[$data['question_type']] ?? '',
            'answer_des' => $data['answer_des'],
            'point_true' => $data['point_true'],
            'point_false' => $data['point_false'],
            'status' => QuestionConstant::ON,
            'orderby' => QuestionConstant::DEFAULT_ORDER,
            'time_display' => $data['time_display'],
            'user_id' => $user->id,
            'practice_id' => $data['practice_id'],
            'sort_number' => $data['sort_number']
        ]);

        return $record;
    }

    public function getDetail($id)
    {
        return $this->questionRepository->findByFilter([
            'id' => $id
        ]);
    }

    public function update($data)
    {
        $record = $this->questionRepository->findByFilter([
            'id' => $data['question_id']
        ]);

        if (empty($record->id)) {
            return [
                'status' => false,
                'messages' => [
                    'id' => 'Bài học không tồn tại.'
                ]
            ];
        } else {
            $typeArray = QuestionConstant::type();

            $record->fill([
                'name' => $data['name'],
                'question_type' => $data['question_type'],
                'type' => $typeArray[$data['question_type']] ?? '',
                'answer_des' => $data['answer_des'],
                'point_true' => $data['point_true'],
                'point_false' => $data['point_false'],
                'time_display' => $data['time_display'],
                'practice_id' => $data['practice_id']
            ]);

            $record->save();

            return [
                'status' => true
            ];
        }
    }

    public function delete($id)
    {
        $record = $this->questionRepository->first([
            'id' => $id
        ]);

        $this->questionRepository->deleteByFilter([
            'id' => $id
        ]);

        return [
            'status' => true
        ];
    }

    public function getPrevById($recordTo)
    {
        return $this->questionRepository->getPrevById($recordTo);
    }
    public function getNextById($recordTo)
    {
        return $this->questionRepository->getNextById($recordTo);
    }

    public function updateNextPosition($recordFrom, $recordTo, $nextToId)
    {
        if (!$nextToId) {
            $this->questionRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => microtime(true) * 10000
            ]);
        } else {
            $this->questionRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $nextToId->sort_number) / 2
            ]);
        }
    }
    public function updatePrevPosition($recordFrom, $recordTo, $prevToId)
    {
        if (!$prevToId) {
            $this->questionRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => $recordTo->sort_number - 1
            ]);
        } else {
            $this->questionRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $prevToId->sort_number) / 2
            ]);
        }
    }
}

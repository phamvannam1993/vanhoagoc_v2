<?php

namespace App\Services;

use App\Enums\AnswerConstant;
use App\Repositories\AnswerRepository;
use App\Repositories\QuestionRepository;

class AnswerService
{
    private $questionRepository;
    private $answerRepository;

    public function __construct(
        AnswerRepository $answerRepository,
        QuestionRepository $questionRepository
    ) {
        $this->answerRepository = $answerRepository;
        $this->questionRepository = $questionRepository;
    }

    public function saveAnswerChoice($data)
    {
        $user = auth()->user();
        $record = $this->questionRepository->first([
            'id' => $data['question_id']
        ]);

        if (!empty($record->id)) {
            $this->answerRepository->deleteByFilter([
                'question_id' => $data['question_id']
            ]);
            foreach ($data['answer'] as $anw) {
                $this->answerRepository->create([
                    'name' => $anw['name'],
                    'right_answer' => $anw['right_answer'] ? 'true' : 'false',
                    'type' => $anw['type'],
                    'user_id' => $user->id,
                    'question_id' => $anw['question_id'],
                    'orderby' => AnswerConstant::DEFAULT_ORDER,
                    'status' => AnswerConstant::ON,
                    'practice_id' => $data['practice_id'],
                    'week_id' => $data['week_id'],
                    'book_id' => $data['book_id'],
                ]);
            }

            return [
                'status' => true,
                'practice_id' => $record->practice_id
            ];
        }

        return [
            'status' => false,
            'messages' => [
                'question' => 'không tồn tại!'
            ]
        ];
    }

    public function getListByQuestion($questionId)
    {
        $list = $this->answerRepository->get([
            'question_id' => $questionId,
            'status' => AnswerConstant::ON
        ]);

        return $list;
    }
}

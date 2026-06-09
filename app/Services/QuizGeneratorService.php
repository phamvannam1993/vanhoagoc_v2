<?php

namespace App\Services;

use App\Repositories\QuestionRepository;
use App\Repositories\AnswerRepository;
use App\Enums\QuestionConstant;
use App\Enums\AnswerConstant;
use Illuminate\Support\Facades\Auth;

class QuizGeneratorService
{
    private $questionRepository;
    private $answerRepository;

    public function __construct(
        QuestionRepository $questionRepository,
        AnswerRepository $answerRepository
    ) {
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
    }

    public function saveQuiz(array $quizData, array $params): array
    {
        try {
            $user = Auth::user();
            $practiceId = $params['practice_id'] ?? null;
            $weekId = $params['week_id'] ?? null;
            $bookId = $params['book_id'] ?? null;

            if (!$practiceId) {
                return [
                    'success' => false,
                    'message' => 'practice_id không được cung cấp.'
                ];
            }

            $createdCount = 0;

            // Save choice type questions
            if (!empty($quizData['chon'])) {
                foreach ($quizData['chon'] as $question) {
                    $createdCount += $this->saveChoiceQuestion(
                        $question,
                        'choice',
                        $practiceId,
                        $weekId,
                        $bookId,
                        $user->id
                    );
                }
            }

            // Save sort type questions
            if (!empty($quizData['sx'])) {
                foreach ($quizData['sx'] as $question) {
                    $createdCount += $this->saveSortQuestion(
                        $question,
                        $practiceId,
                        $weekId,
                        $bookId,
                        $user->id
                    );
                }
            }

            // Save link type questions
            if (!empty($quizData['noi'])) {
                foreach ($quizData['noi'] as $question) {
                    $createdCount += $this->saveLinkQuestion(
                        $question,
                        $practiceId,
                        $weekId,
                        $bookId,
                        $user->id
                    );
                }
            }

            return [
                'success' => true,
                'message' => "Lưu thành công $createdCount câu hỏi.",
                'count' => $createdCount
            ];
        } catch (\Exception $e) {
            \Log::error('Save quiz failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi lưu câu hỏi: ' . $e->getMessage()
            ];
        }
    }

    private function saveChoiceQuestion(array $question, string $questionType, $practiceId, $weekId, $bookId, $userId): int
    {
        try {
            $questionRecord = $this->questionRepository->create([
                'name' => $question['cau_hoi'] ?? $question['tieu_de'],
                'question_type' => $questionType,
                'type' => QuestionConstant::type()[$questionType] ?? 'Trắc nghiệm',
                'answer_des' => json_encode($question['options'] ?? []),
                'point_true' => 10,
                'point_false' => 0,
                'status' => QuestionConstant::ON,
                'orderby' => QuestionConstant::DEFAULT_ORDER,
                'time_display' => 60,
                'user_id' => $userId,
                'practice_id' => $practiceId,
                'week_id' => $weekId,
                'book_id' => $bookId,
                'sort_number' => 0
            ]);

            // Create answers
            $options = $question['options'] ?? [];
            $correctAnswer = $question['dap_an_dung'] ?? null;

            foreach ($options as $index => $option) {
                $answerLetter = chr(65 + $index); // A, B, C, D...
                $isCorrect = $correctAnswer === $answerLetter ? 'true' : 'false';

                $this->answerRepository->create([
                    'name' => $option,
                    'right_answer' => $isCorrect,
                    'type' => 'choice',
                    'question_id' => $questionRecord->id,
                    'user_id' => $userId,
                    'orderby' => AnswerConstant::DEFAULT_ORDER,
                    'status' => AnswerConstant::ON,
                    'practice_id' => $practiceId,
                    'week_id' => $weekId,
                    'book_id' => $bookId
                ]);
            }

            return 1;
        } catch (\Exception $e) {
            \Log::error('Save choice question failed: ' . $e->getMessage());
            return 0;
        }
    }

    private function saveSortQuestion(array $question, $practiceId, $weekId, $bookId, $userId): int
    {
        try {
            $questionRecord = $this->questionRepository->create([
                'name' => $question['tieu_de'],
                'question_type' => 'choice',
                'type' => QuestionConstant::type()['choice'] ?? 'Trắc nghiệm',
                'answer_des' => json_encode(['type' => 'sort', 'items' => $question['options'] ?? []]),
                'point_true' => 10,
                'point_false' => 0,
                'status' => QuestionConstant::ON,
                'orderby' => QuestionConstant::DEFAULT_ORDER,
                'time_display' => 60,
                'user_id' => $userId,
                'practice_id' => $practiceId,
                'week_id' => $weekId,
                'book_id' => $bookId,
                'sort_number' => 0
            ]);

            // Create answers for sort items
            $options = $question['options'] ?? [];
            foreach ($options as $index => $option) {
                $this->answerRepository->create([
                    'name' => $option,
                    'right_answer' => 'false',
                    'type' => 'sort',
                    'question_id' => $questionRecord->id,
                    'user_id' => $userId,
                    'orderby' => $index,
                    'status' => AnswerConstant::ON,
                    'practice_id' => $practiceId,
                    'week_id' => $weekId,
                    'book_id' => $bookId
                ]);
            }

            return 1;
        } catch (\Exception $e) {
            \Log::error('Save sort question failed: ' . $e->getMessage());
            return 0;
        }
    }

    private function saveLinkQuestion(array $question, $practiceId, $weekId, $bookId, $userId): int
    {
        try {
            $questionRecord = $this->questionRepository->create([
                'name' => $question['tieu_de'],
                'question_type' => 'choice',
                'type' => QuestionConstant::type()['choice'] ?? 'Trắc nghiệm',
                'answer_des' => json_encode(['type' => 'link', 'cot_a' => $question['cot_a'] ?? [], 'cot_b' => $question['cot_b'] ?? []]),
                'point_true' => 10,
                'point_false' => 0,
                'status' => QuestionConstant::ON,
                'orderby' => QuestionConstant::DEFAULT_ORDER,
                'time_display' => 60,
                'user_id' => $userId,
                'practice_id' => $practiceId,
                'week_id' => $weekId,
                'book_id' => $bookId,
                'sort_number' => 0
            ]);

            // Create answers for link
            $cotA = $question['cot_a'] ?? [];
            $cotB = $question['cot_b'] ?? [];

            // Add cot_a items
            foreach ($cotA as $index => $item) {
                $this->answerRepository->create([
                    'name' => $item,
                    'right_answer' => 'false',
                    'type' => 'link_a',
                    'question_id' => $questionRecord->id,
                    'user_id' => $userId,
                    'orderby' => $index,
                    'status' => AnswerConstant::ON,
                    'practice_id' => $practiceId,
                    'week_id' => $weekId,
                    'book_id' => $bookId
                ]);
            }

            // Add cot_b items
            foreach ($cotB as $index => $item) {
                $this->answerRepository->create([
                    'name' => $item,
                    'right_answer' => 'false',
                    'type' => 'link_b',
                    'question_id' => $questionRecord->id,
                    'user_id' => $userId,
                    'orderby' => $index,
                    'status' => AnswerConstant::ON,
                    'practice_id' => $practiceId,
                    'week_id' => $weekId,
                    'book_id' => $bookId
                ]);
            }

            return 1;
        } catch (\Exception $e) {
            \Log::error('Save link question failed: ' . $e->getMessage());
            return 0;
        }
    }
}

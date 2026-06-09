<?php

namespace App\Imports;

use App\Enums\AnswerConstant;
use App\Enums\QuestionConstant; // ON, DEFAULT_ORDER
use App\Models\Answer;
use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;

class QuestionsImport implements ToCollection, WithStartRow
{
    public int $imported = 0;

    public function __construct(
        private int $practiceId,
        private ?int $appId,
        private ?int $bookId,
        private ?int $weekId,
        private int $userId,
    ) {}

    public function startRow(): int
    {
        return 2; // skip header
    }

    public function collection(Collection $rows)
    {
        $lastSort = Question::orderByDesc('sort_number')->value('sort_number') ?? 0;

        foreach ($rows as $row) {
            $content    = trim((string) ($row[1] ?? '')); // col B (0-indexed)
            $timeDisplay = (int) ($row[2] ?? 0);           // col C
            $loai       = trim((string) ($row[3] ?? ''));  // col D
            $answerE    = trim((string) ($row[4] ?? ''));  // col E
            $answerF    = trim((string) ($row[5] ?? ''));  // col F
            $answerG    = trim((string) ($row[6] ?? ''));  // col G
            $correctIdx = (int) ($row[7] ?? 0);            // col H

            if (empty($content)) continue;

            $questionType = ($loai === 'Trắc nghiệm') ? 'choice' : 'button';
            $typeName     = ($loai === 'Trắc nghiệm') ? 'Trắc nghiệm' : 'Từ khoá';

            $lastSort += 1000;
            $question = Question::create([
                'name'          => $content,
                'question_type' => $questionType,
                'type'          => $typeName,
                'answer_des'    => '',
                'point_true'    => 10,
                'point_false'   => 5,
                'status'        => QuestionConstant::ON,
                'orderby'       => QuestionConstant::DEFAULT_ORDER,
                'time_display'  => $timeDisplay,
                'user_id'       => $this->userId,
                'practice_id'   => $this->practiceId,
                'sort_number'   => $lastSort,
            ]);

            if ($questionType === 'choice') {
                $answers = array_values(array_filter([$answerE, $answerF, $answerG], fn($a) => $a !== ''));
                foreach ($answers as $i => $answerName) {
                    Answer::create([
                        'name'         => $answerName,
                        'right_answer' => ($i + 1 === $correctIdx) ? 'true' : 'false',
                        'type'         => $questionType,
                        'user_id'      => $this->userId,
                        'question_id'  => $question->id,
                        'orderby'      => AnswerConstant::DEFAULT_ORDER,
                        'status'       => AnswerConstant::ON,
                        'practice_id'  => $this->practiceId,
                        'week_id'      => $this->weekId,
                        'book_id'      => $this->bookId,
                    ]);
                }
            }

            $this->imported++;
        }
    }
}

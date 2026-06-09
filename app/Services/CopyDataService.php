<?php

namespace App\Services;

use App\Enums\PracticeConstant;
use App\Enums\WeekConstant;
use App\Repositories\AnswerRepository;
use App\Repositories\AppRepository;
use App\Repositories\BookRepository;
use App\Repositories\PracticeRepository;
use App\Repositories\QuestionEditorRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\WeekRepository;

class CopyDataService
{
    public function __construct(
        private AppRepository $appRepository,
        private BookRepository $bookRepository,
        private WeekRepository $weekRepository,
        private PracticeRepository $practiceRepository,
        private QuestionEditorRepository $questionEditorRepository,
        private QuestionRepository $questionRepository,
        private AnswerRepository $answerRepository
    ) {
    }

    public function handleCopyBook($fromBookIds, $toAppId)
    {
        $toApp = $this->appRepository->findOrFail($toAppId);
        if (!empty($toApp)) {
            $this->__copyBook($fromBookIds, [], $toApp);
        }
    }

    public function handleCopyWeek($fromPracticeIds, $toWeekId)
    {
        $toWeek = $this->weekRepository->findOrFail($toWeekId);

        if (!empty($toWeek)) {
            $this->__copyPractice($fromPracticeIds, [], $toWeek);
        }
    }

    public function handleCopyQuestionEditor($fromQuestionEditorIds, $toPracticeId)
    {
        $toPractice = $this->practiceRepository->findOrFail($toPracticeId);

        if (!empty($toPractice)) {
            $this->__copyQuestionEditor($fromQuestionEditorIds, [], $toPractice);
        }
    }

    public function handleDuplicateQuestionEditor($fromQuestionIds)
    {
        $fromQuestions = $this->questionEditorRepository->whereIn('id', $fromQuestionIds)->get();

        $toPractice = $this->practiceRepository->findOrFail(data_get($fromQuestions->first(), 'practice_id'));
        if (!empty($toPractice)) {
            $this->__copyQuestionEditor($fromQuestionIds, [], $toPractice);
        }
    }

    private function __copyAnswer($fromAnswerIds = [], $fromAnswers = [], $question)
    {
        if (empty($fromAnswers)) {
            $fromAnswers = $this->answerRepository->whereIn('id', $fromAnswerIds)->get();
        }
        foreach ($fromAnswers as $answer) {
            $cloneAnswer = $answer->replicate();

            $cloneAnswer->book_id = $question->book_id;
            $cloneAnswer->week_id = $question->week_id;
            $cloneAnswer->practice_id = $question->practice_id;
            $cloneAnswer->question_id = $question->id;

            $cloneAnswer->save();
        }
    }

    private function __copyQuestion($fromQuestionIds = [], $fromQuestions = [], $practice)
    {
        if (empty($fromQuestions)) {
            $fromQuestions = $this->questionRepository->whereIn('id', $fromQuestionIds)->get();
        }

        $sortNumber = $this->questionRepository->max('sort_number');

        foreach ($fromQuestions as $question) {
            $sortNumber += 1000;
            $cloneQuestion = $question->replicate();

            $cloneQuestion->book_id = $practice->book_id;
            $cloneQuestion->week_id = $practice->week_id;
            $cloneQuestion->practice_id = $practice->id;
            $cloneQuestion->sort_number = $sortNumber;

            $cloneQuestion->save();

            $this->__copyAnswer([], $question->answers, $cloneQuestion);
        }
    }

    private function __copyQuestionEditor($fromQuestionEditorIds = [], $fromQuestionEditors = [], $practice)
    {
        if (empty($fromQuestionEditors)) {
            $fromQuestionEditors = $this->questionEditorRepository->whereIn('id', $fromQuestionEditorIds)->get();
        }

        $sortNumber = $this->questionEditorRepository->max('sort_number');

        foreach ($fromQuestionEditors as $question) {
            $sortNumber += 1000;
            $cloneQuestion = $question->replicate();

            $cloneQuestion->book_id = $practice->book_id;
            $cloneQuestion->week_id = $practice->week_id;
            $cloneQuestion->practice_id = $practice->id;
            $cloneQuestion->sort_number = $sortNumber;

            $cloneQuestion->save();
        }
    }

    private function __copyPractice($fromPracticeIds = [], $fromPractices = [], $toWeek)
    {
        if (empty($fromPractices)) {
            $fromPractices = $this->practiceRepository->with(['questionEditors', 'questions.answers'])->whereIn('id', $fromPracticeIds)->get();
        }

        $sortNumber = $this->practiceRepository->max('sort_number');
        $numberPractice = $this->practiceRepository->where('week_id', $toWeek->id)->max('numberPractice');

        foreach ($fromPractices as $practice) {
            $sortNumber += 1000;
            $clonePractice = $practice->replicate();

            $clonePractice->book_id = $toWeek->book_id;
            $clonePractice->week_id = $toWeek->id;
            $clonePractice->sort_number = $sortNumber;
            $clonePractice->numberPractice = $numberPractice + 1;
            $clonePractice->practice_id = PracticeConstant::PREFIX_ID . $clonePractice->numberPractice;

            $clonePractice->save();

            $this->__copyQuestion([], $practice->questions, $clonePractice);
            $this->__copyQuestionEditor([], $practice->questionEditors, $clonePractice);
        }
    }

    private function __copyWeek($fromWeekIds, $fromWeeks = [], $toBook)
    {
        if (empty($fromWeeks)) {
            $fromWeeks = $this->weekRepository->with(['practices'])->whereIn('id', $fromWeekIds)->get();
        }

        $sortNumber = $this->weekRepository->max('sort_number');
        $numberWeek = $this->weekRepository->where('book_id', $toBook->id)->max('numberWeek');

        foreach ($fromWeeks as $week) {
            $sortNumber += 1000;
            $cloneWeek = $week->replicate();
            $cloneWeek->book_id = $toBook->id;
            $cloneWeek->sort_number = $sortNumber;
            $cloneWeek->numberWeek = $numberWeek + 1;
            $cloneWeek->week_id = WeekConstant::PREFIX_ID . $cloneWeek->numberWeek;
            $cloneWeek->save();

            $this->__copyPractice([], $week->practices, $cloneWeek);
        }
    }

    private function __copyBook($fromBookIds, $fromBooks = [], $toApp)
    {
        if (empty($fromBooks)) {
            $fromBooks = $this->bookRepository->with(['weeks'])->whereIn('id', $fromBookIds)->get();
        }
        $sortNumber = $this->bookRepository->max('sort_number');
        foreach ($fromBooks as $book) {
            $sortNumber += 1000;
            $cloneBook = $book->replicate();
            $cloneBook->app_id = $toApp->id;
            $cloneBook->sort_number = $sortNumber;
            $cloneBook->save();

            $this->__copyWeek([], $book->weeks, $cloneBook);
        }
    }
}

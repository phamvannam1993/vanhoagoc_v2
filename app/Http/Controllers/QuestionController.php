<?php

namespace App\Http\Controllers;

use App\Imports\QuestionsImport;
use App\Models\Question;
use App\Services\AnswerService;
use App\Services\PracticeService;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class QuestionController extends Controller
{
    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    public function index(Request $request, PracticeService $practiceService)
    {
        $data = $request->all();
        $list = $practiceService->getAll($data);

        return Inertia::render('Question/Index', [
            'list' => $list,
            'query' => $request->query()
        ]);
    }

    public function answer(Request $request)
    {
        $questionId = $request->question_id ?? '';
        $question = $this->questionService->getDetail($questionId);

        return Inertia::render('Question/Answer', [
            'query' => $request->query(),
            'question' => $question
        ]);
    }

    public function jsonList(Request $request, QuestionService $questionService)
    {
        $params = $request->all([
            'practice_id',
            'search'
        ]);

        $list = $questionService->getList($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function jsonAnswer(Request $request, AnswerService $answerService)
    {
        $questionId = $request->get('question_id');
        $list = $answerService->getListByQuestion($questionId);

        foreach ($list as $item) {
            $item['right_answer'] = $item['right_answer'] === 'true' ? 1 : 0;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'list' => $list
            ]
        ]);
    }

    public function storeAnswer(Request $request,  AnswerService $answerService)
    {
        $params = $request->all([
            'question_id',
            'answer',
            'practice_id',
            'week_id',
            'book_id'
        ]);

        $result = $answerService->saveAnswerChoice($params);

        if (empty($result['status'])) {
            return response()->json($result);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('questions.index', ['practice_id' => $result['practice_id']])
                ]
            ]);
        }
    }

    public function editAnswer(Request $request): Response
    {
        $questionId = $request->question_id ?? '';
        $question = $this->questionService->getDetail($questionId);

        return Inertia::render('Question/EditAnswer', [
            'query' => $request->query(),
            'question' => $question
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Question/Create', [
            'query' => $request->query(),
        ]);
    }

    public function store(Request $request, QuestionService $questionService)
    {
        $params = $request->all([
            'name',
            'question_type',
            'answer_des',
            'point_true',
            'point_false',
            'time_display',
            'practice_id',
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'question_id'
        ]);

        $questionId = null;
        if (empty($params['question_id'])) {
            $lastSort = Question::orderByDesc('sort_number')->value('sort_number');
            $params['sort_number'] = $lastSort ? $lastSort + 1000 : 1000;
            $record = $questionService->store($params);
            $questionId = $record->id ?? null;
        } else {
            $questionService->update($params);
            $questionId = $params['question_id'];
        }

        return response()->json([
            'status' => true,
            'data' => [
                'question_id' => $questionId,
                'redirectUrl' => route(
                    'questions.index',
                    [
                        'practice_id' => $params['practice_id'],
                        'app_id' => $params['app_id'],
                        'book_id' => $params['book_id'],
                        'week_id' => $params['week_id'],
                    ]
                )
            ]
        ]);
    }

    public function edit(Request $request, QuestionService $questionService)
    {
        $questionId = $request->get('question_id');

        $record = $questionService->getDetail($questionId);

        return Inertia::render('Question/Edit', [
            'query' => $request->query(),
            'record' => $record
        ]);
    }

    public function delete($id, Request $request)
    {
        $result = $this->questionService->delete($id);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
            ]);
        }
    }

    public function importQuestions(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);

        $import = new QuestionsImport(
            practiceId: (int) $request->input('practice_id'),
            appId:      (int) $request->input('app_id'),
            bookId:     (int) $request->input('book_id'),
            weekId:     (int) $request->input('week_id'),
            userId:     auth()->id(),
        );

        Excel::import($import, $request->file('file'));

        return response()->json(['status' => true, 'imported' => $import->imported]);
    }

    public function updatePosition(Request $request)
    {
        $fromId = $request->from_id;
        $toId = $request->to_id;
        $recordFrom =$this->questionService->getDetail($fromId);
        $recordTo = $this->questionService->getDetail($toId);
        $prevToId = $this->questionService->getPrevById($recordTo);
        $nextToId = $this->questionService->getNextById($recordTo);

        if ($recordFrom->sort_number < $recordTo->sort_number) {
            $this->questionService->updateNextPosition($recordFrom,$recordTo, $nextToId);
        } else {
            $this->questionService->updatePrevPosition($recordFrom,$recordTo, $prevToId);
        }

        return response()->json([
            'status' => true
        ]);
    }
}

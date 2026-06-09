<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\BookService;
use App\Models\Week;
use App\Models\Book;
use App\Models\Question;
use App\Models\Answer;
use App\Models\QuestionEditor;
use App\Models\Practice;
use App\Repositories\BookRepository;
use App\Services\AppService;
use App\Services\WeekService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WeekController extends Controller
{
    protected $bookService;
    protected $weekService;
    protected $bookRepository;

    public function __construct(BookService $bookService, WeekService $weekService, BookRepository $bookRepository)
    {
        $this->bookService = $bookService;
        $this->weekService = $weekService;
        $this->bookRepository = $bookRepository;

    }
    public function index(Request $request)
    {
        $appId = $request->app_id;
        $bookId = $request->get('book_id');
        $bookList = Book::where('app_id', $appId)->get();
        $book = $this->bookRepository->findOrFail($bookId);
        $book->load('app');
        return Inertia::render('Week/Index', [
            'bookList' => $bookList,
            'book' => $book,
            'query' => $request->query()
        ]);
    }

    public function jsonList(Request $request, WeekService $weekService)
    {
        $params = $request->all();
        $list = $weekService->getList($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function create(Request $request, WeekService $weekService)
    {
        $numberWeek = 1;
        $week = Week::where('book_id', $request->book_id)
            ->orderByRaw('CAST(numberWeek AS UNSIGNED) DESC')
            ->first();
        if($week) {
            $numberWeek = $week->numberWeek+1;
        }
        return Inertia::render('Week/Create', [
            'numberWeek' => $numberWeek,
            'query' => $request->query(),
        ]);
    }

    public function store(Request $request, WeekService $weekService)
    {
        $params = $request->all([
            'book_id',
            'numberWeek',
            'name',
            'img'
        ]);
        $params['sort_number'] = microtime(true) * 10000;
        $result = $weekService->store($params);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('weeks.index', ['book_id' => $params['book_id'], 'app_id' => $request->app_id])
                ]
            ]);
        }
    }

    public function edit($id, Request $request, WeekService $weekService)
    {
        $record = $weekService->getDetail($id);
        $record->img = $record->img ? Helper::getCloudFront($record->img) : null;

        if (empty($record->id)) {
            abort(404);
        }

        $numberOfWeekOptions = $weekService->getNumberOfWeekArray();

        return Inertia::render('Week/Edit', [
            'record' => $record,
            'numberOfWeekOptions' => $numberOfWeekOptions,
            'query' => $request->query(),
        ]);
    }
    public function detail($id, Request $request)
    {
        $record = $this->weekService->getDetail($id);

        if (empty($record->id)) {
            abort(404);
        }

        $numberOfWeekOptions = $this->weekService->getNumberOfWeekArray();

        return Inertia::render('Week/Detail', [
            'record' => $record,
            'numberOfWeekOptions' => $numberOfWeekOptions,
            'query' => $request->query(),
        ]);
    }

    public function update(Request $request, WeekService $weekService)
    {
        $params = $request->all([
            'id',
            'numberWeek',
            'name',
            'delete_file',
            'img'
        ]);

        $result = $weekService->update($params);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages'],
                'params' => $params
            ]);
        } else {
            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('weeks.index', ['book_id' => $result['book_id']])
                ]
            ]);
        }
    }

    public function delete($id, WeekService $weekService)
    {
        $result = $weekService->delete($id);

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

    public function visible(Request $request, WeekService $weekService)
    {
        $params = $request->all([
            'status',
            'id'
        ]);

        $weekService->updateVisible($params);

        return response()->json([
            'status' => true
        ]);
    }

    public function updatePosition(Request $request)
    {
        $fromId = $request->from_id;
        $toId = $request->to_id;
        $recordFrom = $this->weekService->getDetail($fromId);
        $recordTo = $this->weekService->getDetail($toId);
        $prevToId = $this->weekService->getPrevById($recordTo);
        $nextToId = $this->weekService->getNextById($recordTo);

        if ($recordFrom->sort_number < $recordTo->sort_number) {
            $this->weekService->updateNextPosition($recordFrom, $recordTo, $nextToId);
        } else {
            $this->weekService->updatePrevPosition($recordFrom, $recordTo, $prevToId);
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function copyData(Request $request) {
        $book_id = $request->book_id;
        $week_ids = $request->week_ids;
        foreach($week_ids as $week) {
            $week_id = $week['id'];
            $week = Week::where("id", $week_id)->first()->toArray();
            if(!empty($week)) {
                $this->handleCopy($week, $book_id);
            }
        }
        return response()->json([
            'status' => true
        ]);
    }

    public function handleCopy($week, $book_id) {
        $lastWeek = Week::where('book_id', $book_id)->orderBy('numberWeek', 'DESC')->first();
        $week_id = 'tuan1';
        $numberWeek = 1;
        if($lastWeek) {
            $numberWeek = $lastWeek->numberWeek + 1;
            $week_id = 'tuan'.$numberWeek;
        }
        $newWeek = $week;
        unset($newWeek['id']);
        unset($newWeek['created_at']);
        unset($newWeek['updated_at']);
        $newWeek['week_id'] = $week_id;
        $newWeek['book_id'] = $book_id;
        $newWeek['numberWeek'] = $numberWeek;
        $result = Week::create($newWeek);
        if($result) {
            $practices = Practice::where('week_id', $week['id'])->get()->toArray();
            if(!empty($practices)) {
                foreach($practices as $practice) {
                    $practiceNew = $practice;
                    unset($practiceNew['id']);
                    unset($practiceNew['created_at']);
                    unset($practiceNew['updated_at']);
                    $practiceNew['week_id'] = $result->id;
                    $practiceNew['book_id'] = $book_id;
                    $resultPra = Practice::create($practiceNew);
                    if($resultPra) {
                        $questions = Question::where('practice_id', $practice['id'])->get()->toArray();
                        if(!empty($questions)) {
                            foreach($questions as $question) {
                                $questionNew = $question;
                                unset($questionNew['id']);
                                unset($questionNew['created_at']);
                                unset($questionNew['updated_at']);
                                unset($questionNew['beauty_time_display']);
                                $questionNew['week_id'] = $result->id;
                                $questionNew['book_id'] = $book_id;
                                $questionNew['practice_id'] = $resultPra->id;
                                $resultQues = Question::create($questionNew);
                                if($resultQues) {
                                    $answers = Answer::where('question_id', $question['id'])->get()->toArray();
                                    if(!empty($answers)) {
                                        foreach( $answers as $answer) {
                                            $answerNew = $answer;
                                            unset($answerNew['id']);
                                            unset($answerNew['created_at']);
                                            unset($answerNew['updated_at']);
                                            $answerNew['week_id'] = $result->id;
                                            $answerNew['book_id'] = $book_id;
                                            $answerNew['practice_id'] = $resultPra->id;
                                            $answerNew['question_id'] = $resultQues->id;
                                            $resultQues = Answer::create($answerNew);
                                        }
                                    }
                                }
                            }
                        }
                        $questionEditors = QuestionEditor::where('practice_id', $practice['id'])->get()->toArray();

                        if(!empty($questionEditors)) {
                            foreach($questionEditors as $questionEditor) {
                                $questionEditorNew = $questionEditor;
                                unset($questionEditorNew['id']);
                                unset($questionEditorNew['created_at']);
                                unset($questionEditorNew['updated_at']);
                                $questionEditorNew['week_id'] = $result->id;
                                $questionEditorNew['book_id'] = $book_id;
                                $questionEditorNew['practice_id'] = $resultPra->id;
                                QuestionEditor::create($questionEditorNew);
                            }
                        }
                    }
                }
            }
        }
    }

    public function deleteMany(Request $request)
    {
        $week_ids = $request->week_ids;
        foreach($week_ids as $week) {
            $week_id = $week['id'];
            Week::where('id', $week_id)->delete();
            Practice::where('week_id', $week_id)->delete();
            Question::where('week_id', $week_id)->delete();
            Answer::where('week_id', $week_id)->delete();
            QuestionEditor::where('week_id', $week_id)->delete();
        }
        return response()->json([
            'status' => true
        ]);
    }
}

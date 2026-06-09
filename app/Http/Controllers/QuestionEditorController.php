<?php

namespace App\Http\Controllers;

use App\Enums\QuestionConstant;
use App\Enums\QuestionTemplateConstant;
use App\Services\ExerciseService;
use App\Services\PracticeService;
use App\Services\QuestionEditorService;
use App\Models\QuestionEditor;
use App\Models\QuestionTemplate;
use App\Repositories\PracticeRepository;
use App\Services\CopyDataService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;

class QuestionEditorController extends Controller
{
    protected $questionEditorService;

    protected $practiceRepository;
    protected $copyDataService;

    public function __construct(QuestionEditorService $questionEditorService, PracticeRepository $practiceRepository, CopyDataService $copyDataService)
    {
        $this->questionEditorService = $questionEditorService;
        $this->practiceRepository = $practiceRepository;
        $this->copyDataService = $copyDataService;

    }
    public function index(Request $request, PracticeService $practiceService)
    {
        // todo trong list cần trả thêm type của câu hỏi, loại game, mẫu, path của mẫu câu hỏi
        $list = $practiceService->getAll($request->all());
        $practiceId = $request->get('practice_id');
        $practice = $this->practiceRepository->findOrFail($practiceId);
        $practice->load('week.book.app');
        return Inertia::render('Question/Exercise', [
            'list' => $list,
            'practice' => $practice,
            'query' => $request->query()
        ]);
    }

    public function jsonList(Request $request, QuestionEditorService $questionEditorService)
    {
        $params = $request->all([
            'practice_id',
            'search',
            'pageSize'
        ]);

        $list = $questionEditorService->getList($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function exercises(Request $request): Response
    {
        //        todo list to filter, comment
        return Inertia::render('Question/Exercise', [
            'query' => $request->query(),
        ]);
    }
    public function createExercise(Request $request, ExerciseService $exerciseService)
    {
        $template = $exerciseService->getByApp($request);
        $listType = QuestionTemplateConstant::type();
        $order = array_flip(array_keys($listType));
        usort($template['type_list'], function($x, $y) use ($order) {
            return ($order[$x['value']] ?? PHP_INT_MAX) <=> ($order[$y['value']] ?? PHP_INT_MAX);
        });
        $practiceId = $request->get('practice_id');
        $practice = $this->practiceRepository->findOrFail($practiceId);
        $practice->load('week.book.app');

        return Inertia::render('Question/CreateExercise', [
            'query' => $request->query(),
            'list' => $template['list'],
            'types' => $template['type_list'],
            'practice' => $practice
        ]);
    }

    public function importExercise(Request $request): Response
    {
        //       todo Cần form chuẩn để khi import để người dùng biết
        return Inertia::render('Question/ImportExercise', [
            'query' => $request->query()
        ]);
    }

    public function storeExercise(Request $request)
    {
        $request->validate([
            'file'        => 'required|file',
            'practice_id' => 'required',
        ]);

        $practiceId = $request->input('practice_id');
        $appId      = $request->input('app_id');
        $bookId     = $request->input('book_id');
        $weekId     = $request->input('week_id');

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheet       = $spreadsheet->getSheet(0);
        $rows        = $sheet ? ($sheet->toArray(null, true, true, false) ?? []) : [];

        $letterToIndex = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5];
        $templateCache = [];
        $errors        = [];
        $count         = 0;

        $lastSort = QuestionEditor::orderByDesc('sort_number')->value('sort_number') ?? 0;

        foreach (array_slice($rows, 1) as $rowIndex => $row) {
            $templateCode = trim($row[1] ?? '');
            $title        = trim($row[3] ?? '');

            if ($templateCode === '' && $title === '') {
                continue;
            }

            if (!isset($templateCache[$templateCode])) {
                $templateCache[$templateCode] = QuestionTemplate::where('playable', $templateCode)
                    ->where('app_id', $appId)
                    ->first();
            }
            $template = $templateCache[$templateCode];

            if (!$template) {
                $errors[] = "Dòng " . ($rowIndex + 2) . ": Không tìm thấy template mã '$templateCode'";
                continue;
            }

            $record = [
                'template'        => $template->id,
                'practice_id'     => $practiceId,
                'book_id'         => $bookId,
                'week_id'         => $weekId,
                'title'           => $title,
                'question_type'   => $template->type === 'game_chon' ? 'text' : null,
                'question_val'    => null,
                'answers'         => [],
                'answer_connects' => [],
                'is_multi_result' => 0,
                'user_id'         => auth()->id(),
                'tem_playable_id' => 'cau hoi',
                'pcnl'            => '',
                'ndgd'            => '',
            ];

            switch ($template->type) {
                case 'game_chon':
                    // E: câu hỏi, F-K: đáp án A-F, L: đáp án đúng
                    $record['question_val'] = trim($row[4] ?? '');
                    $correctStr    = trim($row[11] ?? '');
                    $correctLetters = array_filter(array_map('trim', explode(',', $correctStr)));
                    $correctIndexes = array_values(array_filter(array_map(
                        fn($l) => $letterToIndex[strtoupper($l)] ?? null,
                        $correctLetters
                    ), fn($v) => $v !== null));

                    $answers = [];
                    foreach ([5, 6, 7, 8, 9, 10] as $idx => $col) {
                        $val = trim($row[$col] ?? '');
                        if ($val === '') continue;
                        $answers[] = [
                            'type'        => 'text',
                            'value'       => $val,
                            'checked'     => in_array($idx, $correctIndexes),
                            'inputNumber' => '',
                            'answer_text' => $val,
                        ];
                    }
                    $record['answers']         = $answers;
                    $record['is_multi_result'] = count($correctIndexes) > 1 ? 1 : 0;
                    break;

                case 'game_noi':
                    // E-H: vế trái (answers), I-L: vế phải (answer_connects)
                    // inputNumber phải khớp nhau để game biết cặp nào nối với nhau
                    $answers        = [];
                    $answerConnects = [];
                    $pairIndex      = 0;
                    foreach ([4, 5, 6, 7] as $pos => $col) {
                        $leftVal  = trim($row[$col] ?? '');
                        $rightVal = trim($row[$col + 4] ?? ''); // I, J, K, L
                        if ($leftVal === '' && $rightVal === '') continue;
                        $answers[] = [
                            'type'        => 'text',
                            'value'       => $leftVal,
                            'checked'     => false,
                            'inputNumber' => (string)$pairIndex,
                            'answer_text' => $leftVal,
                        ];
                        $answerConnects[] = [
                            'type'        => 'text',
                            'value'       => $rightVal,
                            'inputNumber' => (string)$pairIndex,
                            'answer_text' => $rightVal,
                        ];
                        $pairIndex++;
                    }
                    $record['answers']         = $answers;
                    $record['answer_connects'] = $answerConnects;
                    break;

                case 'game_sap_xep':
                    // E-I: các items theo thứ tự đúng (tối đa 5 items)
                    $answers = [];
                    foreach ([4, 5, 6, 7, 8] as $pos => $col) {
                        $val = trim($row[$col] ?? '');
                        if ($val === '') continue;
                        $answers[] = [
                            'type'        => 'text',
                            'value'       => $val,
                            'checked'     => false,
                            'inputNumber' => (string)($pos + 1),
                            'answer_text' => $val,
                        ];
                    }
                    $record['answers'] = $answers;
                    break;

                default:
                    $errors[] = "Dòng " . ($rowIndex + 2) . ": Loại template '{$template->type}' chưa được hỗ trợ import";
                    continue 2;
            }

            $lastSort += 1000;
            $record['sort_number'] = $lastSort;
            QuestionEditor::create($record);
            $count++;
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'count'       => $count,
                'errors'      => $errors,
                'redirectUrl' => route('questionEditors.index', [
                    'practice_id' => $practiceId,
                    'app_id'      => $appId,
                    'book_id'     => $bookId,
                    'week_id'     => $weekId,
                ]),
            ],
        ]);
    }

    public function storeExerciseQuestion(Request $request)
    {
        // todo save data
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'redirectUrl' => route('questions.index', [
                    'practice_id' => $params['practice_id'],
                    'app_id' => $params['app_id'],
                    'book_id' => $params['book_id'],
                    'week_id' => $params['week_id'],
                ])
            ]
        ]);
    }

    public function storeGame(Request $request)
    {
        // todo tạo game, có thể chia thành các function tạo từng loại game khác nhau đang để gộp
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'template_id',
        ]);

        return response()->json([
            'status' => true,
            'data' => [
                'redirectUrl' => route('questions.index', [
                    'practice_id' => $params['practice_id'],
                    'app_id' => $params['app_id'],
                    'book_id' => $params['book_id'],
                    'week_id' => $params['week_id'],
                ])
            ]
        ]);
    }

    public function delete($id, QuestionEditorService $questionEditorService)
    {
        $result = $questionEditorService->delete($id);

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

    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids');
        QuestionEditor::whereIn('id', $ids)->delete();
        return response()->json([
            'status' => true,
        ]);
    }

    public function visible(Request $request, QuestionEditorService $questionEditorService)
    {
        $params = $request->all([
            'status',
            'id'
        ]);

        $questionEditorService->updateVisible($params);

        return response()->json([
            'status' => true
        ]);
    }

    public function updatePosition(Request $request)
    {
         $fromId = $request->from_id;
        $toId = $request->to_id;

        $this->questionEditorService->dragPosition($fromId, $toId);

        return response()->json([
            'status' => true
        ]);
    }

    public function resetPosition(Request $request)
    {
        $this->questionEditorService->resetPosition($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function copyData(Request $request) {

        $this->copyDataService->handleCopyQuestionEditor($request->question_editor_ids, $request->to_practice_id);
        return response()->json([
            'status' => true
        ]);
    }

    public function duplicate(Request $request) {
        $this->copyDataService->handleDuplicateQuestionEditor($request->question_ids);
        return response()->json([
            'status' => true
        ]);
    }
}

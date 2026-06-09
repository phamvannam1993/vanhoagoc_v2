<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Services\ExerciseService;
use App\Repositories\QuestionTemplateRepository;

class QuestionUploadController extends Controller
{
    protected ExerciseService $exerciseService;
    protected QuestionTemplateRepository $questionTemplateRepository;

    public function __construct(ExerciseService $exerciseService, QuestionTemplateRepository $questionTemplateRepository)
    {
        $this->exerciseService = $exerciseService;
        $this->questionTemplateRepository = $questionTemplateRepository;
    }

    public function show(Request $request)
    {
        return Inertia::render('Question/UploadQuestions', [
            'query' => $request->query(),
        ]);
    }

    public function saveQuestion(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:chon,sx,noi',
                'question' => 'required|array',
                'practice_id' => 'required|integer',
                'week_id' => 'nullable|integer',
                'book_id' => 'nullable|integer',
                'app_id' => 'required|integer',
            ]);

            $type = $validated['type'];
            $question = $validated['question'];
            $practiceId = $validated['practice_id'];
            $weekId = $validated['week_id'];
            $bookId = $validated['book_id'];
            $appId = $validated['app_id'];

            // Convert quiz data to ExerciseService format
            $data = $this->convertQuizToExerciseFormat(
                $type,
                $question,
                $practiceId,
                $weekId,
                $bookId,
                $appId
            );

            $this->exerciseService->saveQuestionEditor($data);

            return response()->json([
                'success' => true,
                'message' => 'Câu hỏi đã được lưu thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Save question failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lưu câu hỏi: ' . $e->getMessage()
            ], 500);
        }
    }

    private function convertQuizToExerciseFormat(string $type, array $question, int $practiceId, ?int $weekId, ?int $bookId, int $appId): array
    {
        // Get template ID from database using ma_cau_hoi
        $maCauHoi = $question['ma_cau_hoi'] ?? $question['playable'] ?? '';
        $templateId = $this->getTemplateIdByQuestionCode($maCauHoi, $appId, $type);

        $data = [
            'title' => $question['tieu_de'] ?? '',
            'practice_id' => $practiceId,
            'week_id' => $weekId,
            'book_id' => $bookId,
            'template_id' => $templateId,
            'question_type' => 1, // text type
            'question_val' => $question['cau_hoi'] ?? $question['tieu_de'] ?? 'Câu hỏi',
            'audio_val' => null,
            'question_video_url' => null,
            'pcnl' => '',
            'ndgd' => '',
            'competency_id' => null,
            'competency_component_id' => null,
            'pcnl_detail' => null,
            'educational_content_id' => null,
            'ndgd_requirement' => null,
            'background' => null,
            'file_background_name' => null,
            'image_db' => null,
            'file_reading_name' => null,
            'audio_ques_val' => null,
            'question_array' => null,
            'question_img' => null,
            'link' => '',
            'tem_playable_id' => 'cau hoi',
            'content' => $question['trich_dan_dap_an'] ?? null,
        ];

        if ($type === 'chon') {
            // Multiple choice format
            $listAnswer = [];
            $options = $question['options'] ?? [];
            $correctAnswer = $question['dap_an_dung'] ?? '';
            $correctAnswers = array_map('trim', explode(',', $correctAnswer));

            foreach ($options as $idx => $opt) {
                // Extract content: noi_dung > value > (whole object if string)
                if (is_array($opt)) {
                    $optText = $opt['noi_dung'] ?? $opt['value'] ?? '';
                    $optKey = $opt['key'] ?? $opt['chu_cai'] ?? chr(65 + $idx);
                } else {
                    $optText = $opt;
                    $optKey = chr(65 + $idx);
                }

                $isChecked = in_array($optKey, $correctAnswers) ? 1 : 0;
                $listAnswer[] = [
                    'type_answer' => 1, // text type
                    'answer_val' => $optText,
                    'checked' => $isChecked,
                    'inputNumber' => '',
                    'answer_text' => $optText
                ];
            }
            $data['listAnswer'] = json_encode($listAnswer);
        } elseif ($type === 'sx') {
            // Sort/arrange format
            $listAnswer = [];
            $options = $question['options'] ?? [];

            foreach ($options as $idx => $opt) {
                if (is_array($opt)) {
                    $optText = $opt['noi_dung'] ?? $opt['value'] ?? '';
                } else {
                    $optText = $opt;
                }

                $listAnswer[] = [
                    'type_answer' => 1,
                    'answer_val' => $optText,
                    'checked' => 0,
                    'inputNumber' => $idx + 1,
                    'answer_text' => $optText
                ];
            }
            $data['listAnswer'] = json_encode($listAnswer);
        } elseif ($type === 'noi') {
            // Link/matching format
            $cotA = $question['cot_a'] ?? [];
            $cotB = $question['cot_b'] ?? [];

            Log::info('Quiz noi data received', [
                'cot_a_count' => count($cotA),
                'cot_b_count' => count($cotB),
                'cot_a_sample' => array_slice($cotA, 0, 2),
                'cot_b_sample' => array_slice($cotB, 0, 2)
            ]);

            // Validate that cot_a and cot_b are not empty
            if (empty($cotA) || empty($cotB)) {
                Log::error('Missing cot_a or cot_b for noi question', [
                    'cot_a_empty' => empty($cotA),
                    'cot_b_empty' => empty($cotB),
                    'question' => $question
                ]);
            }

            $listAnswer = [];
            $listAnswerConnect = [];

            // Create listAnswer from cot_a (left column items)
            foreach ($cotA as $idx => $item) {
                if (is_array($item)) {
                    $itemText = $item['noi_dung'] ?? $item['value'] ?? $item['text'] ?? '';
                } else {
                    $itemText = (string)$item;
                }

                // First item has empty inputNumber, others have 1, 2, 3...
                $inputNum = $idx === 0 ? '' : (string)$idx;

                $listAnswer[] = [
                    'type' => 'text',
                    'type_answer' => 1,
                    'value' => $itemText,
                    'answer_val' => $itemText,
                    'checked' => false,
                    'inputNumber' => $inputNum,
                    'answer_text' => $itemText
                ];
            }

            // Create listAnswerConnect from cot_b (right column items)
            foreach ($cotB as $idx => $item) {
                if (is_array($item)) {
                    $itemText = $item['noi_dung'] ?? $item['value'] ?? $item['text'] ?? '';
                } else {
                    $itemText = (string)$item;
                }

                $listAnswerConnect[] = [
                    'type' => 'text',
                    'type_answer' => 1,
                    'value' => $itemText,
                    'answer_val' => $itemText,
                    'inputNumber' => (string)$idx,
                    'answer_text' => $itemText
                ];
            }

            Log::info('Created listAnswer and listAnswerConnect', [
                'listAnswer_count' => count($listAnswer),
                'listAnswerConnect_count' => count($listAnswerConnect)
            ]);

            $data['listAnswer'] = json_encode($listAnswer);
            $data['listAnswerConnect'] = json_encode($listAnswerConnect);
        }

        return $data;
    }

    private function getTemplateIdByQuestionCode(?string $maQuestionCode, int $appId, string $fallbackType): int
    {
        // Query database to get template ID by playable code
        Log::info('getTemplateIdByQuestionCode', ['maQuestionCode' => $maQuestionCode, 'appId' => $appId]);

        if (!empty($maQuestionCode)) {
            try {
                // Query question_templates table directly
                $templateId = DB::table('question_templates')
                    ->where('app_id', $appId)
                    ->where('playable', $maQuestionCode)
                    ->value('id');

                if ($templateId) {
                    Log::info('Found template from DB', ['id' => $templateId, 'playable' => $maQuestionCode, 'appId' => $appId]);
                    return intval($templateId);
                } else {
                    Log::warning('Template not found in DB', ['appId' => $appId, 'playable' => $maQuestionCode]);
                }
            } catch (\Exception $e) {
                Log::error('Error querying template: ' . $e->getMessage(), ['appId' => $appId, 'playable' => $maQuestionCode]);
            }
        }

        // Fallback: use type parameter to get template ID
        $fallbackId = $this->getTemplateIdByType($fallbackType);
        Log::info('Using fallback template ID', ['fallbackType' => $fallbackType, 'fallbackId' => $fallbackId]);

        return $fallbackId;
    }

    private function getTemplateIdByType(string $type): int
    {
        // Fallback: Map quiz types to template IDs
        $typeMap = [
            'chon' => 1, // Choose correct
            'sx' => 4,   // Arrange/Sort
            'noi' => 3,  // Link/Connect
        ];
        return $typeMap[$type] ?? 1;
    }
}

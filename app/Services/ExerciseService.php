<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Helpers\MediaHelper;
use App\Models\QuestionEditor;
use App\Repositories\CourseRepository;
use App\Repositories\QuestionEditorRepository;
use App\Repositories\QuestionTemplateRepository;
use Illuminate\Http\Request;

class ExerciseService
{
    private $questionTemplateRepository;
    private $questionEditorRepository;

    public function __construct(
        QuestionTemplateRepository $questionTemplateRepository,
        QuestionEditorRepository $questionEditorRepository
    ) {
        $this->questionTemplateRepository = $questionTemplateRepository;
        $this->questionEditorRepository = $questionEditorRepository;
    }

    public function getList()
    {
        $list = $this->questionTemplateRepository->all();

        $types = [];

        foreach ($list as $item) {
            if (empty($types[$item->type])) {
                $types[$item->type] = [
                    'value' => $item->type,
                    'label' => $item->type_label
                ];
            }
        }

        return [
            'list' => $list,
            'type_list' => array_values($types)
        ];
    }

    public function getByApp(Request $request)
    {
        $appId = $request->app_id ?? '';
        $list = $this->questionTemplateRepository->getByApp($appId);
        $types = [];

        foreach ($list as $item) {
            $item->image_url = Helper::getCloudFront($item->image);
            if (empty($types[$item->type])) {
                $types[$item->type] = [
                    'value' => $item->type,
                    'label' => $item->type_label
                ];
            }
        }

        return [
            'list' => $list,
            'type_list' => array_values($types)
        ];
    }

    public function getListQuestionTemplate($data)
    {
        $list = $this->questionTemplateRepository->getListByFilters($data);

        foreach ($list as $item) {
            $item->image = $item->image ? Helper::getCloudFront($item->image) : null;
            $item->app_id = $item->app_id ?? null;
        }

        return $list;
    }

    public function getQuestionTemplate($templateId)
    {
        $record = $this->questionTemplateRepository->first([
            'id' => $templateId
        ]);

        return $record;
    }

    public function getDetailById($questionEditorId)
    {
        $record = $this->questionEditorRepository->findOrFail($questionEditorId);
        $template = $this->questionTemplateRepository->findOrFail($record->template);
        $isYouTubeVideo = MediaHelper::isYouTubeUrl($record->question_video_url) === 0;
        $data = [
            'id' => $record->id,
            'title' => $record->title,
            'question_type' => $this->getIntByType($record->question_type),
            'question_val' => $record->question_val,
            'question_val_show' => Helper::getCloudFront($record->question_val),
            'audio_ques_val' =>  Helper::getCloudFront($record->audio_ques_val),
            'audio_val' => $record->audio_val ? Helper::getCloudFront($record->audio_val) : null,
            'audio_val_db' => $record->audio_val ?? null,
            'question_video_url' => $isYouTubeVideo ? $record->question_video_url : Helper::getCloudFront($record->question_video_url),
            'question_video_url_db' => $record->question_video_url,
            'isYouTubeVideo' => MediaHelper::isYouTubeUrl($record->question_video_url) === 0,
            'pcnl' => $record->pcnl,
            'ndgd' => $record->ndgd,
            'competency_id' => $record->competency_id,
            'competency_component_id' => $record->competency_component_id,
            'pcnl_detail' => $record->pcnl_detail,
            'educational_content_id' => $record->educational_content_id,
            'ndgd_requirement' => $record->ndgd_requirement,
            'background' => $record->background,
            'file_background_name' => $record->file_background_name,
            'background_show' => Helper::getCloudFront($record->background),
            'reading_val' => $record->reading_val,
            'reading_doc' => $record->reading_doc,
            'file_reading_name' => $record->file_reading_name,
            'reading_val_show' => Helper::getCloudFront($record->reading_val),

            'template_id' => $record->tem_playable_id,
            'practice_id' => $record->practice_id,
            'book_id' => $record->book_id,
            'week_id' => $record->week_id,

            'answer_array' => $record->answer_array,
            'question_array' => $record->question_array,
            'right_answer_array' => $record->right_answer_array,
            'img' => $record->img,
            'img_show' => Helper::getCloudFront($record->img)
        ];

        $listAnswer = [];

        $answers = $record->answers;

        foreach ($answers as $i => $answer) {
            $type = $answer['type'];
            $value = $answer['value'];
            $listAnswer[] = [
                'type_answer' => $this->getIntByType($type),
                'answer_val' => $this->getCorrectValue($type, $value),
                'answer_text' => $answer['answer_text'] ?? '',
                'inputAnswer' => $this->getIntByType($type) === 3 ? $answer['answer_text'] : $this->getCorrectValue($type, $value),
                'image_answer_show' =>  $this->getCorrectValue($type, $value),
                'image_answer_db' => $value,
                'audio_answer_show' => $this->getCorrectValue($type, $value),
                'audio_answer_db' => $value,
                'answer_val_db' => $value,
                'checked' => $answer['checked'],
                'inputNumber' => $answer['inputNumber'] ?? ''
            ];
        }

        //listAnswerConnect
        $listAnswerConnect = [];
        $answerConnects = $record->answer_connects;
        foreach ($answerConnects as $i => $answerConnect) {
            $type = $answerConnect['type'];
            $value = $answerConnect['value'];

            $listAnswerConnect[] = [
                'type_answer' => $this->getIntByType($type),
                'answer_val' => $this->getCorrectValue($type, $value),
                'answer_text' => $answerConnect['answer_text'] ?? '',
                'inputAnswer' =>  $this->getIntByType($type) === 3 ? '' : $this->getCorrectValue($type, $value),
                'answer_val_db' => $value,
                'image_answer_show' => $this->getCorrectValue($type, $value),
                'image_answer_db' => $value,
                'audio_answer_show' => $this->getCorrectValue($type, $value),
                'audio_answer_db' => $value,
                'checked' => false,
                'inputNumber' => $answerConnect['inputNumber'] ?? ''
            ];
        }

        $data['listAnswer'] = $listAnswer;
        $data['listAnswerConnect'] = $listAnswerConnect;

        return [
            'template' => $template,
            'data' => $data
        ];
    }

    public function getCorrectValue($type, $value)
    {
        if (empty($value) || $type == 'text') {
            return $value;
        }

        $value = Helper::getCloudFront($value);

        return $value;
    }

    public function getViewPathForCreate($type)
    {
        $view = '';

        switch ($type) {
            case 'game_chon':
                $view = 'Question/New';
                break;
            case 'game_keo':
                $view = 'SelectSample/CreateDragDrop';
                break;
            case 'game_noi':
                $view = 'SelectSample/CreateConnectSentence';
                break;
            case 'game_sap_xep':
                $view = 'SelectSample/CreateArrange';
                break;
            case 'game_ghep_anh':
                $view = 'SelectSample/CreateMatchPhoto';
                break;
            case 'game_chon_sap_xep':
                $view = 'SelectSample/CreateChooseArrange';
                break;
            case 'game_dien_viet':
                $view = 'SelectSample/CreateDragDrop';
                break;
        }

        return $view;
    }

    public function getViewPathForEdit($type)
    {
        $view = '';

        switch ($type) {
            case 'game_chon':
                $view = 'SelectSample/EditChooseCorrect';
                break;
            case 'game_keo':
                $view = 'SelectSample/EditDragDrop';
                break;
            case 'game_noi':
                $view = 'SelectSample/EditConnectSentence';
                break;
            case 'game_sap_xep':
                $view = 'SelectSample/EditArrange';
                break;
            case 'game_chon_sap_xep':
                $view = 'SelectSample/EditChooseArrange';
                break;
            case 'game_ghep_anh':
                $view = 'SelectSample/EditMatchPhoto';
                break;
        }

        return $view;
    }

    public function saveQuestionEditor($data, $id = null)
    {
        // note
        // game keo (2-*) game noi (3-*) game sap xep (4-*)
        // game keo question_type mặc định là 2 (image)
        $questionType = isset($data['question_type']) ? $this->getTypeByInt($data['question_type']) : null;
        $info = [
            'title' => $data['title'], // 1-2, 2-1, 3-1, 4-1
            'question_type' => $questionType,  // 1-3
            'question_val' => $data['question_val'] ?? null, // 1-5, 2-2
            'audio_val' => $data['audio_val'] ?? null, // 1-7, 2-7, 4-3
            'question_video_url' => $data['question_video_url'] ?? null, // 1-8, 4-5
            'pcnl' => $data['pcnl'], // 1-9, 2-9
            'ndgd' => $data['ndgd'], // 1-10, 2-10
            'competency_id' => $data['competency_id'] ?: null,
            'competency_component_id' => $data['competency_component_id'] ?: null,
            'pcnl_detail' => $data['pcnl_detail'] ?? null,
            'educational_content_id' => $data['educational_content_id'] ?: null,
            'ndgd_requirement' => $data['ndgd_requirement'] ?? null,
            'background' => $data['background'] ?? null, // 1-13, 2-13
            'file_background_name' => $data['file_background_name'] ?? null, // 1-13, 2-13
            'reading_val' => $data['image_db'] ?? null, // 1-14, 2-14
            'reading_doc' => $data['content'] ?? null, // 1-14, 2-14
            'file_reading_name' => $data['file_reading_name'] ?? null, // 1-14, 2-14
            'audio_ques_val' => $questionType == 'audio' ? $data['audio_ques_val'] : null, // 1-15

            'tem_playable_id' => 'cau hoi', // dump
            'template' => $data['template_id'],
            'practice_id' => $data['practice_id'],
            'book_id' => $data['book_id'],
            'week_id' => $data['week_id'],
            'user_id' => auth()->user()->id,
            'link' => $data['link'],
            'question_array' => $data['question_array'] ?? null, // 2-4
            'img' => $data['question_img'] ?? null
        ];

        $count = 0;

        $answers = [];
        if (!empty($data['listAnswer'])) {
            $listAnswer = json_decode($data['listAnswer'], true);

            foreach ($listAnswer as $i => $item) {
                $type = $this->getTypeByInt($item['type_answer']);
                if (!empty($type)) {
                    $answers[] = [
                        'type' => $type,
                        'value' => $item['answer_val'],
                        'checked' => empty($item['checked']) ? false : true,
                        'inputNumber' => empty($item['inputNumber']) ? '' : $item['inputNumber'],
                        'answer_text' => empty($item['answer_text']) ? '' : $item['answer_text']
                    ];

                    if (!empty($item['checked'])) {
                        $count++;
                    }
                }
            }
        }
        $info['answers'] = $answers;

        $info['is_multi_result'] = $count > 1 ? 1 : 0;

        $answerConnects = [];
        if (!empty($data['listAnswerConnect'])) {
            $listAnswer = json_decode($data['listAnswerConnect'], true);
            foreach ($listAnswer as $i => $item) {
                $type = $this->getTypeByInt($item['type_answer']);
                if (!empty($type)) {
                    $answerConnects[] = [
                        'type' => $type,
                        'value' => $item['answer_val'],
                        'inputNumber' => $item['inputNumber'],
                        'answer_text' => empty($item['answer_text']) ? '' : $item['answer_text']
                    ];
                }
            }
        }
        $info['answer_connects'] = $answerConnects;

        if (!empty($id)) {
            $this->questionEditorRepository->updateByFilters(['id' => $id], $info);
        } else {
            $lastSort = QuestionEditor::orderByDesc('sort_number')->value('sort_number');
            $info['sort_number'] = $lastSort ? $lastSort + 1000 : 1000;
            $this->questionEditorRepository->create($info);
        }

        return true;
    }

    public function getTypeByInt($value)
    {
        $types = [
            1 => 'text',
            2 => 'image',
            3 => 'audio'
        ];

        return empty($types[$value]) ? null : $types[$value];
    }

    public function getIntByType($type)
    {
        $types = [
            'text' => 1,
            'image' => 2,
            'audio' => 3
        ];

        return empty($types[$type]) ? null : $types[$type];
    }
}

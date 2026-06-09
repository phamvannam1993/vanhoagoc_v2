<?php

namespace App\Services;

use App\Enums\BookConstant;
use App\Enums\PracticeConstant;
use App\Enums\UserAccessModuleConstant;
use App\Helpers\BinaryHelper;
use App\Helpers\Helper;
use App\Helpers\MediaHelper;
use App\Repositories\AnswerRepository;
use App\Repositories\BookRepository;
use App\Repositories\PracticeRepository;
use App\Repositories\QuestionEditorRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\QuestionTemplateRepository;
use App\Repositories\TemplateRepository;
use App\Repositories\WeekRepository;
use App\Services\Traits\ImageManagerTrait;
use Illuminate\Support\Facades\Storage;

class BookService
{
    use ImageManagerTrait;

    private $bookRepository;
    private $weekRepository;
    private $practiceRepository;
    private $templateRepository;
    private $questionEditorRepository;
    private $questionRepository;
    private $answerRepository;
    private $questionTemplateRepository;

    public function __construct(
        BookRepository $bookRepository,
        WeekRepository $weekRepository,
        PracticeRepository $practiceRepository,
        QuestionEditorRepository $questionEditorRepository,
        TemplateRepository $templateRepository,
        QuestionRepository $questionRepository,
        AnswerRepository $answerRepository,
        QuestionTemplateRepository $questionTemplateRepository
    ) {
        $this->bookRepository = $bookRepository;
        $this->weekRepository = $weekRepository;
        $this->practiceRepository = $practiceRepository;
        $this->questionEditorRepository = $questionEditorRepository;
        $this->templateRepository = $templateRepository;
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
        $this->questionTemplateRepository = $questionTemplateRepository;
    }

    public function getList($data)
    {
        [$bookIds, $parentBookIds] = app(UserAccessModuleService::class)->getBookIds(data_get($data, 'app_id'));

        $user = auth()->user();
        $isAdmin = $user->userType->type === \App\Models\UserType::TYPE_ADMIN;
        $list = $this->bookRepository->searchByFilters($data, array_merge($bookIds, $parentBookIds), $isAdmin);

        foreach ($list as $item) {
            $weekOfBook = $this->weekRepository->get(['book_id' => $item->id]);
            $item->has_weeks = count($weekOfBook) > 0;
            $item->count_week = count($weekOfBook);
            $item->img = $item->img ? Helper::getCloudFront($item->img) : null;
            if (in_array($item->id, $parentBookIds)) {
                $item->access_permission = UserAccessModuleConstant::PERMISSION_VIEW;
            }else {
                $item->access_permission = UserAccessModuleConstant::PERMISSION_FULL;
            }
        }
        return $list;
    }

    public function getListAll()
    {
        $list = $this->bookRepository->all();

        return $list;
    }

    public function store($data)
    {
        $user = auth()->user();

        $this->bookRepository->create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'name' => $data['name'],
            'app_id' => $data['app_id'],
            'bo_sach' => $data['bo_sach'],
            'status' => BookConstant::ON,
            'lop' => 'lop' . $data['lop'],
            'img' => $data['img'],
            'sort_number' => $data['sort_number']
        ]);

        return [
            'status' => true
        ];
    }

    public function getDetail($id)
    {
        return $this->bookRepository->findOrFail($id);
    }

    public function update($data)
    {
        $user = auth()->user();

        $record = $this->bookRepository->findByFilter([
            'id' => $data['id']
        ]);

        if (empty($record->id)) {
            return [
                'status' => false,
                'messages' => [
                    'id' => 'Book không tồn tại.'
                ]
            ];
        }

        // if (!empty($data['delete_file'])) {
        //     $this->deleteFile(BookConstant::MEDIA_PATH, $record->img);
        //     $record->img = null;
        // }

        $record->user_id = $user->id;
        $record->title = $data['title'];
        $record->name = $data['name'];
        $record->app_id = $data['app_id'];
        $record->bo_sach = $data['bo_sach'];
        $record->lop = 'lop' . $data['lop'];
        $record->img = $data['img'];
        $record->save();

        return [
            'status' => true
        ];
    }

    public function returnFail($message)
    {
        return [
            'status' => false,
            'message' => $message
        ];
    }

    public function getBookAll($params)
    {
        $book = $this->bookRepository->first([
            'name' => $params['name']
        ]);

        if (empty($book)) {
            return $this->returnFail('Sách không tồn tại');
        }

        $book_id_string = $book->bo_sach . '.' . $book->lop . '.' . $book->name . '.quyen1';

        $week = $this->weekRepository->get([
            'book_id' => $book->id
        ], [
            'orderBy' => 'sort_number asc',
            'withArr' => [
                'practices' => function($q){
                    return $q->orderBy('sort_number', 'ASC');
                },
                'practices.questionEditors'=> function($q){
                    return $q->orderBy('sort_number', 'ASC');
                },
                'practices.questionEditors.templateQuestion',
                'practices.questionEditors.competency',
                'practices.questionEditors.competencyComponent',
                'practices.questionEditors.educationalContent'
            ]
        ]);

        $week_data = [];
        foreach ($week as $key => $week_val) {
            $practice = $week_val->practices;

            $practice_data = [];
            foreach ($practice as $key_p => $practice_val) {
                $question = $practice_val->questionEditors;
                $question_data = [];
                foreach ($question as $key_q => $question_val) {
                    $template = $question_val->templateQuestion;

                    $answer_data = [];
                    $right_answer = [];
                    $answers = $question_val->answers;
                    foreach ($answers as $i => $answer) {
                        $type = $answer['type'];
                        $value = $answer['value'];

                        $answer_data[] = [
                            'type' =>  $type,
                            'A' => $question_val->getRealAnswer($type, $value),
                            'text' => $answer['answer_text'] ?? ''
                        ];
                        switch ($template->type) {
                            case 'game_noi':
                                // the answer inside answer connect
                                break;
                            case 'game_keo':
                                $right_answer[] = $answer['inputNumber'];
                                break;
                            case 'game_chon':
                                if (!empty($answer['checked'])) {
                                    $right_answer[] = "" . $i;
                                }
                            default:
                                if ($template->type !== 'game_chon') {
                                    $right_answer[] = $answer['inputNumber'];
                                }
                                break;
                        }
                    }

                    $question_data_2 = [
                        Helper::getCloudFront($question_val->img)
                    ];
                    $answer_2_data = [];

                    $answer_connect_data = [];
                    $answerConnects = $question_val->answer_connects;
                    foreach ($answerConnects as $answerConnect) {
                        $type = $answerConnect['type'];
                        $value = $answerConnect['value'];
                        $answer_connect_data[] = [
                            'type' =>  $type,
                            'A' => $question_val->getRealAnswerConnect($type, $value),
                            'text' => $answerConnect['answer_text'] ?? ''
                        ];

                        $right_answer[] = $answerConnect['inputNumber'] ?? '';
                    }

                    // url background
                    $url_img_question_background = Helper::getCloudFront($question_val->background);

                    //urldoc
                    $url_img_reading_val = [
                        'urlimg' =>  $question_val->reading_val ? Helper::getCloudFront($question_val->reading_val) : '',
                        'text' =>  $question_val->reading_doc ?? '',
                    ];

                    //audioTitle
                    $url_audio_val = Helper::getCloudFront($question_val->audio_val);

                    //audioQustion
                    $url_audio_ques_val = Helper::getCloudFront($question_val->audio_ques_val);

                    //url video
                    $url_video_s = Helper::getCloudFront($question_val->video_val);
                    $link = "https://vanhoagoc.com.vn/questionEditors/edit-game?app_id=$book->app_id&book_id=$question_val->book_id&id=$question_val->id&practice_id=$question_val->practice_id&template_id=$question_val->template&week_id=$question_val->week_id";

                    // Format pcnl: "CODE_PCNL.CODE_COMPONENT.(pcnl_detail)"
                    $pcnlParts = array_filter([
                        optional($question_val->competency)->code,
                        optional($question_val->competencyComponent)->code,
                        $question_val->pcnl_detail,
                    ]);
                    $pcnlFormatted = implode('.', $pcnlParts) ?: $question_val->pcnl;

                    // Format ndgd: "CODE_NDGD.(ndgd_requirement)"
                    $ndgdParts = array_filter([
                        optional($question_val->educationalContent)->code,
                        $question_val->ndgd_requirement,
                    ]);
                    $ndgdFormatted = implode('.', $ndgdParts) ?: $question_val->ndgd;

                    $question_data[$key_q] = array(
                        'tem_playable_id' => $book_id_string . '.' . $week_val->week_id . '.' . $practice_val->practice_id . '.' . $question_val->tem_playable_id,
                        'playable' => $template->playable ?? '',
                        'question_id' => $question_val->id,
                        'pcnl' => $pcnlFormatted,
                        'ndgd' => $ndgdFormatted,
                        'question_video_url' => Helper::getCloudFront($question_val->question_video_url), //btodo
                        'is_multi_result' => $question_val->is_multi_result,
                        'questiondata' => array(
                            'background' => $url_img_question_background,
                            'question' => array(
                                'type' => $question_val->question_type,
                                'Q' => MediaHelper::getCorrectQuestionByType($question_val->question_type, $question_val->question_val),
                            ),
                            'answers' => $answer_data,
                            'answers_array' => json_decode($question_val->answer_array),
                            'answers_2' => $answer_2_data,
                            'answer_2_array' => json_decode($question_val->answer_2_array),
                            'answer_3_array' => json_decode($question_val->answer_3_array),
                            'question_2' =>  $question_data_2,
                            'question_array' => json_decode($question_val->question_array),
                            'answers_connect' => $answer_connect_data,
                            'urldoc' =>  $url_img_reading_val,
                            'clicks' => false,
                            'title' => $question_val->title,
                            'obj' => json_decode($question_val->obj),
                            'reading' => '',
                            'number_image_cut' => $question_val->number_image_cut,
                            'audioTitle' => $url_audio_val,
                            'audioQuestion' => $url_audio_ques_val,
                            'video' => $url_video_s,
                            'right_answer' => $right_answer,
                            'right_answer_array' => json_decode($question_val->right_answer_array),
                            'link' => $link
                        )
                    );
                }

                //cover_image
                $url_cover_image =  MediaHelper::getCorrectValueByType('image', $practice_val->img);

                //lesson_video
                $url_lesson_video = MediaHelper::getCorrectValueByType('video', $practice_val->lesson_video);

                //lesson_noi
                $url_lesson_noi =  MediaHelper::getCorrectValueByType('audio', $practice_val->lesson_noi);

                //lesson_doc
                $url_lesson_doc =  $practice_val->lesson_doc;

                //lesson_doc2
                $url_lesson_doc2 =  $practice_val->lesson_doc2;

                //pdf
                $url_lesson_pdf = $practice_val->pdf ? Helper::getCloudFront($practice_val->pdf) : null;

                // avatar
                $url_avatar = $practice_val->avatar ? Helper::getCloudFront($practice_val->avatar) : null;

                // background
                $settingAdvance = $practice_val->setting_advance ? json_decode($practice_val->setting_advance, true) : null;
                $background = $settingAdvance ? json_decode($settingAdvance['background']) : null;
                $colorNotPractice = $settingAdvance ? json_decode($settingAdvance['color_not_practice']) : null;
                $colorDonePractice = $settingAdvance ? json_decode($settingAdvance['color_done_practice']) : null;

                $is_lesson_doc_text = false;
                $is_lesson_doc2_text = false;
                if (!MediaHelper::isImageFile($practice_val->lesson_doc)) {
                    $is_lesson_doc_text = true;
                    $url_lesson_doc = $practice_val->lesson_doc;
                }
                if (!MediaHelper::isImageFile($practice_val->lesson_doc2)) {
                    $is_lesson_doc2_text = true;
                    $url_lesson_doc2 = $practice_val->lesson_doc2;
                }
                if ($practice_val->taptrung != 'true') {
                    $taptrung = false;
                } else {
                    $taptrung = true;
                }
                $practice_data[$key_p] = array(
                    'practice_id' => $book_id_string . '.' . $week_val->week_id . '.' . $practice_val->practice_id,
                    'practice_id_tool' => $practice_val->id,
                    'cover_image' =>  $url_cover_image,
                    'name' =>  $practice_val->name,
                    'status' =>  $practice_val->status,
                    'lessons' =>  array(
                        'lesson_video' =>  $url_lesson_video,
                        'lesson_noi' =>  $url_lesson_noi,
                        'lesson_doc' => $url_lesson_doc,
                        'lesson_doc2' => $url_lesson_doc2,
                        'lesson_vr' => '',
                        'lesson_pdf' => $url_lesson_pdf,
                        'avatar' => $url_avatar,
                        'background' => $background,
                        'color_not_practice' => $colorNotPractice,
                        'color_done_practice' => $colorDonePractice,
                        'is_lesson_doc_text' => $is_lesson_doc_text,
                        'is_lesson_doc2_text' => $is_lesson_doc2_text,
                        'taptrung' => $taptrung,
                    ),
                    'tem_playables' => $question_data,

                );
            }

            //cover_image
            $url_week_img =  Helper::getCloudFront($week_val->img);

            $week_data[$key] = array(
                'week_id' => $book_id_string . '.' . $week_val->week_id,
                'name' => $week_val->name,
                'cover_image' => $url_week_img,
                'valid_day_from' => '1',
                'numberWeek' => $week_val->numberWeek,
                'status' => $week_val->status,
                'practices' => $practice_data,

            );
        }

        //cover_image
        $url_book_img = Helper::getCloudFront($book->img);

        $data = array(
            'book_id' => $book_id_string,
            'name' => $book->title,
            'cover_image' => $url_book_img,
            'created_date' => $book->created_at,
            'idAppvideo' => $book->app_id,
            'status' => $book->status,
            'weeks' => $week_data
        );

        return [
            'status' => true,
            'data' => $data
        ];
    }

    public function getDeepview($params)
    {
        $book =  $this->bookRepository->first([
            'name' => $params['book_name']
        ]);

        if (!$book) {
            return $this->returnFail('Tên sách không tồn tại');
        }

        $week = $this->weekRepository->first([
            'book_id' => $book->id,
            'week_id' => $params['week_id']
        ]);

        if (!$week) {
            return $this->returnFail('Tuần không tồn tại');
        }

        $filters = [
            'book_id' => $book->id,
            'week_id' => $week->id
        ];

        if (!empty($params['practice_id'])) {
            $filters['practice_id'] = $params['practice_id'];
        }
        $practice_list =  $this->practiceRepository->get($filters, ['orderBy' => 'numberPractice asc']);

        $post = [];
        foreach ($practice_list as $key => $practice) {
            // cau hoi
            $question_data = [];
            $question_list = $this->questionRepository->get([
                'practice_id' => $practice->id
            ], [
                'orderBy' => 'time_display asc',
            ])->groupBy('time_display');

            $i = -1;
            foreach ($question_list as $key_time => $question_time) {

                $i++;
                $question_data[$i]['timeStop'] = $key_time;
                $question_list = [];
                foreach ($question_time as $key_q => $question) {
                    // cau tra loi
                    $answer_list_data = [];
                    $answer_with_id_data = [];
                    $right_answer = '';
                    $id_right_answer = '';
                    $answer_list = $this->answerRepository->get([
                        'question_id' => $question->id
                    ]);

                    foreach ($answer_list as $key_a => $answer) {
                        $answer_list_data[$key_a] = $answer->name;
                        $answer_with_id_data[$key_a][$answer->id] = $answer->name;
                        if ($answer->right_answer == 'true') {
                            $right_answer = $answer->name;
                            $id_right_answer = $answer->id;
                        }
                    }

                    $question_list[$key_q]['id'] = $question->id;
                    $question_list[$key_q]['time_display'] = $question->time_display;
                    $question_list[$key_q]['point_false'] = $question->point_false;
                    $question_list[$key_q]['point_true'] = $question->point_true;
                    $question_list[$key_q]['answer_des'] = $question->answer_des;
                    $question_list[$key_q]['title'] = $question->title_for_app;
                    $question_list[$key_q]['name'] = $question->name;
                    $question_list[$key_q]['answer'] = $answer_list_data;
                    $question_list[$key_q]['right_answer'] = $right_answer;
                    $question_list[$key_q]['answer_with_id'] = $answer_with_id_data;
                    $question_list[$key_q]['id_right_answer'] = $id_right_answer;
                    $question_list[$key_q]['question_type'] =  $question->question_type;
                }

                $question_data[$i]['questionList'] = $question_list;
            }

            $post[$key]['idVideo'] = $practice->id;
            $post[$key]['namevideo'] = $practice->title;
            $post[$key]['nameFileVideo'] = $practice->lesson_video;
            $post[$key]['urlvideo'] = asset('editor/video/practice/' . $practice->lesson_video);
            $post[$key]['lesson_doc'] = asset('editor/img/practice/' . $practice->lesson_doc);
            $post[$key]['lesson_doc2'] = asset('editor/img/practice/' . $practice->lesson_doc2);
            $post[$key]['data'] = $question_data;
            $post[$key]['practice_id'] = $practice->id;
        }

        $data = [
            'NameApp' => $params['course_id'] ?? '',
            'book_name' => $params['book_name'],
            'book_title' => $book->title,
            'bo_sach' => $book->bo_sach,
            'quyen' => $book->quyen,
            'lop' => $book->lop,
            'week_id' => $week->id,
            'status' => 'on',
            'post' => $post
        ];

        return [
            'status' => true,
            'data' => $data
        ];
    }

    public function delete($id)
    {
        $record = $this->bookRepository->first([
            'id' => $id
        ]);

        // delete media resource
        if (!empty($record->img)) {
            $this->deleteFile(BookConstant::MEDIA_PATH, $record->img);
        }

        $this->bookRepository->deleteByFilter([
            'id' => $id
        ]);

        return [
            'status' => true
        ];
    }

    public function updateVisible($data)
    {
        $this->bookRepository->updateByFilters([
            'id' => $data['id']
        ], [
            'status' => $data['status'] == 'on' ? 'on' : 'off'
        ]);
    }

    public function getPrevById($recordTo)
    {
        return $this->bookRepository->getPrevById($recordTo);
    }
    public function getNextById($recordTo)
    {
        return $this->bookRepository->getNextById($recordTo);
    }

    public function updateNextPosition($recordFrom, $recordTo, $nextToId)
    {
        if (!$nextToId) {
            $this->bookRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => microtime(true) * 10000
            ]);
        } else {
            $this->bookRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $nextToId->sort_number) / 2
            ]);
        }
    }
    public function updatePrevPosition($recordFrom, $recordTo, $prevToId)
    {
        if (!$prevToId) {
            $this->bookRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => $recordTo->sort_number - 1
            ]);
        } else {
            $this->bookRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $prevToId->sort_number) / 2
            ]);
        }
    }

    public function getLessonByApp($appId, $userId, $classId)
    {
        return $this->bookRepository->getLessonByApp($appId, $userId, $classId);
    }
}

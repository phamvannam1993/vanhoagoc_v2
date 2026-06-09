<?php

namespace App\Services;

use App\Helpers\MediaHelper;
use App\Repositories\AnswerRepository;
use App\Repositories\NovelRepository;
use App\Repositories\PracticeRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\WeekRepository;

class NovelService
{
    private $novelRepository;
    private $weekRepository;
    private $practiceRepository;
    private $questionRepository;
    private $answerRepository;

    public function __construct(
        NovelRepository $novelRepository,
        WeekRepository $weekRepository,
        PracticeRepository $practiceRepository,
        QuestionRepository $questionRepository,
        AnswerRepository $answerRepository
    ) {
        $this->novelRepository = $novelRepository;
        $this->weekRepository = $weekRepository;
        $this->practiceRepository = $practiceRepository;
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
    }

    public function getDeepview($request)
    {
        $novelId = $request['novel_id'];
        $novel = $this->novelRepository->first(['id' => $novelId]);

        if (!$novel) {
            return ['success' => false, 'message' => 'Truyện không tồn tại'];
        }

        $weekId = $request['week_id'];  // tuan1
        $week = $this->weekRepository->first([
            'week_id' => $weekId,
            'novel_id' => $novel->id
        ]);

        if (!$week) {
            return ['success' => false, 'message' => 'Tuần không tồn tại'];
        }

        $filters = [
            'novel_id' => $novel->id,
            'week_id' => $week->id
        ];
        if (!empty($request['practice_id'])) {
            $filters['practice_id'] = $request['practice_id'];
        }
        $practice_list =  $this->practiceRepository->get($filters, [
            'orderBy' => 'numberPractice asc'
        ]);

        $post = [];
        foreach ($practice_list as $key => $practice) {
            // cau hoi
            $question_data = [];
            $question_list = $this->questionRepository->get([
                'practice_id' => $practice->id,
            ], [
                'groupBy' => 'time_display',
                'orderBy' => 'time_display asc'
            ]);

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
                    $question_list[$key_q] = $question;
                    $question_list[$key_q]['title'] = $question->title_for_a;
                    $question_list[$key_q]['answer'] = $answer_list_data;
                    $question_list[$key_q]['right_answer'] = $right_answer;
                    $question_list[$key_q]['answer_with_id'] = $answer_with_id_data;
                    $question_list[$key_q]['id_right_answer'] = $id_right_answer;
                }
                $question_data[$i]['questionList'] = $question_list;
            }
            $post[$key]['idVideo'] = $practice->id;
            $post[$key]['namevideo'] = $practice->title;
            $post[$key]['nameFileVideo'] = $practice->lesson_video;
            $post[$key]['url_video'] = isset($practice->lesson_video) && $practice->lesson_video != '' ? config('common.aws.cloud_front_domain') . '/editor/video/practice/' . $practice->lesson_video : '';
            $post[$key]['url_audio'] = isset($practice->lesson_noi) && $practice->lesson_noi != '' ?  config('common.aws.cloud_front_domain') . '/editor/video/practice/' . $practice->lesson_noi : '';

            $post[$key]['is_lesson_doc_text'] = false;
            $post[$key]['is_lesson_doc2_text'] = false;
            $post[$key]['lesson_doc'] = isset($practice->lesson_doc) && $practice->lesson_doc != '' ? asset('editor/img/practice/' . $practice->lesson_doc) : '';
            $post[$key]['lesson_doc2'] = isset($practice->lesson_doc2) && $practice->lesson_doc2 != '' ?  asset('editor/img/practice/' . $practice->lesson_doc2) : '';
            if (!MediaHelper::isImageFile($practice->lesson_doc)) {
                $post[$key]['is_lesson_doc_text'] = true;
                $post[$key]['lesson_doc'] = $practice->lesson_doc;
            }
            if (!MediaHelper::isImageFile($practice->lesson_doc2)) {
                $post[$key]['is_lesson_doc2_text'] = true;
                $post[$key]['lesson_doc2'] = $practice->lesson_doc2;
            }
            $post[$key]['img'] = isset($practice->img) && $practice->img != '' ? config('common.aws.cloud_front_domain') . '/editor/img/practice/' . $practice->img : '';
            $post[$key]['data'] = $question_data;
            $post[$key]['practice_id'] = $practice->id;
        }

        $data = [
            'NameApp' => $request['course_id'],
            'novel_title' => $novel->title,
            'week_id' => $weekId,
            'status' => 'on',
            'post' => $post
        ];

        return $data;
    }
}

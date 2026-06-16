<?php

namespace App\Services;

use App\Enums\AppConstant;
use App\Enums\UserAccessModuleConstant;
use App\Enums\UserTypeConstant;
use App\Helpers\Helper;
use App\Repositories\AppRepository;
use App\Repositories\BookRepository;
use App\Repositories\ClassesRepository;
use App\Repositories\NovelRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\QuestionTemplateRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserAccessModuleRepository;
class AppService
{
    private $appRepository;
    private $permissionRepository;
    private $roleRepository;
    private $bookRepository;
    private $novelRepository;
    private $commentRepository;
    private $questionTemplateRepository;
    private $classesRepository;
    private $userAccessModuleRepository;
    private $userAccessModuleService;

    public function __construct(
        AppRepository $appRepository,
        PermissionRepository $permissionRepository,
        RoleRepository $roleRepository,
        NovelRepository $novelRepository,
        CommentRepository $commentRepository,
        BookRepository $bookRepository,
        QuestionTemplateRepository $questionTemplateRepository,
        ClassesRepository $classesRepository,
        UserAccessModuleService $userAccessModuleService,
        UserAccessModuleRepository $userAccessModuleRepository
    ) {
        $this->appRepository = $appRepository;
        $this->permissionRepository = $permissionRepository;
        $this->roleRepository = $roleRepository;
        $this->bookRepository = $bookRepository;
        $this->novelRepository = $novelRepository;
        $this->commentRepository = $commentRepository;
        $this->questionTemplateRepository = $questionTemplateRepository;
        $this->classesRepository = $classesRepository;
        $this->userAccessModuleRepository = $userAccessModuleRepository;
        $this->userAccessModuleService = $userAccessModuleService;
    }

    public function getList($data, $withoutPoint = false)
    {
        [$appIds, $parentAppIds] = $this->userAccessModuleService->getAppIds();
        $list = $this->appRepository->searchByFilters($data, array_merge($appIds, $parentAppIds), $withoutPoint);
        $user = auth()->user();

        // Bulk load data existence to avoid N+1 queries
        $appIdsList = $list->pluck('id')->toArray();

        $novels = \DB::table('novels')->whereIn('app_id', $appIdsList)->select('app_id')->distinct()->pluck('app_id')->flip();
        $comments = \DB::table('comments')->whereIn('app_id', $appIdsList)->select('app_id')->distinct()->pluck('app_id')->flip();
        $classes = \DB::table('classes')->whereIn('app_id', $appIdsList)->select('app_id')->distinct()->pluck('app_id')->flip();
        $books = \DB::table('book')->whereIn('app_id', $appIdsList)->select('app_id')->distinct()->pluck('app_id')->flip();

        foreach ($list as $item) {
            $hasResult = !$withoutPoint ? ($item->classes_with_points > 0) : true;

            $item->has_result = $hasResult;
            $item->is_book = isset($books[$item->id]);
            $item->is_novel = isset($novels[$item->id]);
            $item->is_comment = isset($comments[$item->id]);
            $item->isClass = isset($classes[$item->id]);

            $item->is_show_comment = in_array($user->user_type_id, [
                UserTypeConstant::TYPE_ADMIN,
                UserTypeConstant::TYPE_EDITOR,
                UserTypeConstant::TYPE_TEACHER,
                UserTypeConstant::TYPE_TEACHER_ADMIN
            ]);

            $item->img = $item->img ? Helper::getCloudFront($item->img) : null;

            if (in_array($item->id, $parentAppIds)) {
                $item->access_permission = UserAccessModuleConstant::PERMISSION_VIEW;
            } else {
                $item->access_permission = UserAccessModuleConstant::PERMISSION_FULL;
            }
        }

        return $list;
    }

    public function getListFull()
    {
        $list = $this->appRepository->all();

        return $list;
    }

    public function getListByRole()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return $this->appRepository->all();
        }else {
            return $this->appRepository->getListByRole();
        }
    }

    public function store($data)
    {
        $user = auth()->user();

        $record = $this->appRepository->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'status' => AppConstant::ON,
            'img' => $data['img'],
            'sort_number' => $data['sort_number']
        ]);

        $record->url = 'p_' . $record->id;
        $record->save();

        $this->permissionRepository->create([
            'title' => $record->name,
            'name' => $record->url,
            'type' => 'app'
        ]);

        $this->roleRepository->create([
            'user_id' => $user->id,
            'user_type_id' => $user->user_type_id,
            'permission' => 'p_' . $record->id
        ]);

        return $record;
    }

    public function update($data)
    {
        $record = $this->appRepository->findByFilter([
            'id' => $data['id']
        ]);
        if (empty($record->id)) {
            return [
                'status' => false,
                'messages' => [
                    'id' => 'app không tồn tại.'
                ]
            ];
        } else {
            $record->name = $data['name'];
            $record->img = $data['img'];
            $record->setting_advance = $data['setting_advance'];
            $record->save();

            return [
                'status' => true
            ];
        }
    }

    public function findAppById($id)
    {
        return $this->appRepository->findOrFail($id);
    }

    public function delete($id)
    {
        $this->appRepository->deleteByFilter([
            'id' => $id
        ]);
    }

    public function returnFail($message)
    {
        return [
            'status' => false,
            'message' => $message
        ];
    }

    public function getAppEditorByBook($params)
    {
        if (!empty($params['id'])) {
            $app = $this->appRepository->first(['id' => $params['id']]);
        } else {
            return $this->returnFail('id trong!');
        }

        $book_list = $this->bookRepository->get([
            'app_id' => $app->id
        ], [
            'orderBy' => 'sort_number asc'
        ]);

        $book_data = [];
        foreach ($book_list as $key_b => $val) {
            $book_id_string = $val->bo_sach . '.' . $val->lop . '.' . $val->name . '.' . $val->quyen;

            $url_img = Helper::getCloudFront($val->img);

            $book_data[$key_b] = array(
                'id' =>  $val->id,
                'title' =>  $val->title,
                'app_id' => $val->app_id,
                'app_name' => $app->name,
                'name' =>  $val->name,
                'book_id' => $book_id_string,
                'bo_sach' =>  $val->bo_sach,
                'status' => $val->status,
                'quyen' => $val->quyen,
                'lop' => $val->lop,
                'cover_image' => $url_img,
                'created_date' => $val->created_at,
            );
        }

        $settingAdvance = $app->setting_advance ? json_decode($app->setting_advance, true) : null;

        if ($settingAdvance) {
            $background = json_decode($settingAdvance['background']);

            if (is_object($background)) {
                $background->value_show = $background->value_show ? Helper::getCloudFront($background->value_show) : null;
                $app->background = $background;
            } else {
                $app->background = null;
            }

            $backgroundSound = json_decode($settingAdvance['background_sound']);

            if (is_object($backgroundSound)) {
                $backgroundSound->value_play = $backgroundSound->value_db ? Helper::getCloudFront($backgroundSound->value_db) : null;
                $app->background_sound = $backgroundSound;
            } else {
                $app->background_sound = null;
            }

            $soundClick = json_decode($settingAdvance['sound_click']);

            if (is_object($soundClick)) {
                $soundClick->value_play = $soundClick->value_db ? Helper::getCloudFront($soundClick->value_db) : null;
                $app->sound_click = $soundClick;
            } else {
                $app->sound_click = null;
            }

            $soundChooseCorrect = json_decode($settingAdvance['sound_choose_correct']);

            if (is_object($soundChooseCorrect)) {
                $soundChooseCorrect->value_play = $soundChooseCorrect->value_db ? Helper::getCloudFront($soundChooseCorrect->value_db) : null;
                $app->sound_choose_correct = ($soundChooseCorrect);
            } else {
                $app->sound_choose_correct = null;
            }

            $soundChooseWrong = json_decode($settingAdvance['sound_choose_wrong']);

            if (is_object($soundChooseWrong)) {
                $soundChooseWrong->value_play = $soundChooseWrong->value_db ? Helper::getCloudFront($soundChooseWrong->value_db) : null;
                $app->sound_choose_wrong = ($soundChooseWrong);
            } else {
                $app->sound_choose_wrong = null;
            }

            $soundOneCorrect = json_decode($settingAdvance['sound_one_correct']);

            if (is_object($soundOneCorrect)) {
                $soundOneCorrect->value_play = $soundOneCorrect->value_db ? Helper::getCloudFront($soundOneCorrect->value_db) : null;
                $app->sound_one_correct = ($soundOneCorrect);
            } else {
                $app->sound_one_correct = null;
            }

            $soundOneWrong = json_decode($settingAdvance['sound_one_wrong']);

            if (is_object($soundOneWrong)) {
                $soundOneWrong->value_play = $soundOneWrong->value_db ? Helper::getCloudFront($soundOneWrong->value_db) : null;
                $app->sound_one_wrong = ($soundOneWrong);
            } else {
                $app->sound_one_wrong = null;
            }

            $soundMultiCorrect = json_decode($settingAdvance['sound_multi_correct']);

            if (is_object($soundMultiCorrect)) {
                $soundMultiCorrect->value_play = $soundMultiCorrect->value_db ? Helper::getCloudFront($soundMultiCorrect->value_db) : null;
                $app->sound_multi_correct = ($soundMultiCorrect);
            } else {
                $app->sound_multi_correct = null;
            }

            $soundMultiWrong = json_decode($settingAdvance['sound_multi_wrong']);

            if (is_object($soundMultiWrong)) {
                $soundMultiWrong->value_play = $soundMultiWrong->value_db ? Helper::getCloudFront($soundMultiWrong->value_db) : null;
                $app->sound_multi_wrong = ($soundMultiWrong);
            } else {
                $app->sound_multi_wrong = null;
            }

            $soundResultExcellent = json_decode($settingAdvance['sound_result_excellent']);

            if (is_object($soundResultExcellent)) {
                $soundResultExcellent->value_play = $soundResultExcellent->value_db ? Helper::getCloudFront($soundResultExcellent->value_db) : null;
                $app->sound_result_excellent = ($soundResultExcellent);
            } else {
                $app->sound_result_excellent = null;
            }

            $soundResultGood = json_decode($settingAdvance['sound_result_good']);

            if (is_object($soundResultGood)) {
                $soundResultGood->value_play = $soundResultGood->value_db ? Helper::getCloudFront($soundResultGood->value_db) : null;
                $app->sound_result_good = ($soundResultGood);
            } else {
                $app->sound_result_good = null;
            }

            $soundResultAverage = json_decode($settingAdvance['sound_result_average']);

            if (is_object($soundResultAverage)) {
                $soundResultAverage->value_play = $soundResultAverage->value_db ? Helper::getCloudFront($soundResultAverage->value_db) : null;
                $app->sound_result_average = ($soundResultAverage);
            } else {
                $app->sound_result_average = null;
            }
        }


        $data = array(
            'id' => $app->id,
            'name' => $app->name,
            'background' => $app->background ?? null,
            'background_sound' => $app->background_sound ?? null,
            'sound_click' => $app->sound_click ?? null,
            'sound_choose_correct' => $app->sound_choose_correct ?? null,
            'sound_choose_wrong' => $app->sound_choose_wrong ?? null,
            'sound_one_correct' => $app->sound_one_correct ?? null,
            'sound_one_wrong' => $app->sound_one_wrong ?? null,
            'sound_multi_correct' => $app->sound_multi_correct ?? null,
            'sound_multi_wrong' => $app->sound_multi_wrong ?? null,
            'sound_result_excellent' => $app->sound_result_excellent ?? null,
            'sound_result_good' => $app->sound_result_good ?? null,
            'sound_result_average' => $app->sound_result_average ?? null,
            'status' => $app->status,
            'created_at' => $app->created_at,
            'updated_at' => $app->updated_at,
            'book_list' => $book_data
        );

        return [
            'status' => true,
            'data' => $data
        ];
    }

    public function updateVisible($data)
    {
        $this->appRepository->updateByFilters([
            'id' => $data['id']
        ], [
            'status' => $data['status'] == 'on' ? 'on' : 'off'
        ]);
    }

    public function getPrevById($recordTo)
    {
        return $this->appRepository->getPrevById($recordTo);
    }
    public function getNextById($recordTo)
    {
        return $this->appRepository->getNextById($recordTo);
    }

    public function updateNextPosition($recordFrom, $recordTo, $nextToId)
    {
        if (!$nextToId) {
            $this->appRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => microtime(true) * 10000
            ]);
        } else {
            $this->appRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $nextToId->sort_number) / 2
            ]);
        }
    }
    public function updatePrevPosition($recordFrom, $recordTo, $prevToId)
    {
        if (!$prevToId) {
            $this->appRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => $recordTo->sort_number - 1
            ]);
        } else {
            $this->appRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $prevToId->sort_number) / 2
            ]);
        }
    }

    public function clone($id)
    {
        // clone app
        $appOld = $this->appRepository->first([
            'id' => $id
        ]);

        if (empty($appOld->id)) {
            return [
                'status' => false,
                'messages' => [
                    'id' => 'id không tồn tại!'
                ]
            ];
        }

        $app = $this->store([
            'name' => $appOld->name . ' (copy)',
            'img' => $appOld->img,
            'sort_number' => $appOld->sort_number + 1
        ]);

        // clone question_templates
        $listOld = $this->questionTemplateRepository->get([
            'app_id' => $appOld->id
        ]);

        foreach ($listOld as $item) {
            $newItem = $item->replicate();
            $newItem->app_id = $app->id;
            $newItem->save();
        }

        return $app;
    }
}

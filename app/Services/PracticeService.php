<?php

namespace App\Services;

use App\Enums\PracticeConstant;
use App\Helpers\Helper;
use App\Helpers\MediaHelper;
use App\Models\QuestionEditor;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Practice;
use App\Repositories\PracticeRepository;
use App\Repositories\QuestionEditorRepository;
use App\Services\Traits\ImageManagerTrait;
use Illuminate\Support\Facades\Log;

class PracticeService
{
    use ImageManagerTrait;

    private $practiceRepository;
    private $questionEditorRepository;

    public function __construct(
        PracticeRepository $practiceRepository,
        QuestionEditorRepository $questionEditorRepository
    ) {
        $this->practiceRepository = $practiceRepository;
        $this->questionEditorRepository = $questionEditorRepository;
    }

    public function getList($data)
    {
        $list = $this->practiceRepository->getListByFilters($data);

        $list->through(function ($value) {
            $value->is_lesson_doc_text = !MediaHelper::isImageFile($value->lesson_doc);
            $value->is_lesson_doc2_text = !MediaHelper::isImageFile($value->lesson_doc2);
            $question = $value->questions->where('status', 'on');
            $value->has_questions = $question->isEmpty() ? 0 : 1;
            $value->count_question = $question->count();
            $questionEditor = $value->questionEditors->where('is_visible', 'on')->first();
            $value->has_question_editors = empty($questionEditor->id) ? 0 : 1;
            $value->has_questions_voice = $value->lesson_noi ? 1 : 0;
            return $value;
        });

        return $list;
    }

    public function getAll($data)
    {
        $list = $this->practiceRepository->getListByFilters($data, true);

        return $list;
    }

    public function getNumberPracticeForCreate($data)
    {
        $record = $this->practiceRepository->findByFilter([
            'week_id' => $data['week_id'],
            'book_id' => $data['book_id']
        ], [
            'order' => " numberPractice DESC "
        ]);

        if (!empty($record->id)) {
            $startNumber = $record->numberPractice + 1;
        } else {
            $startNumber = 1;
        }

        return $startNumber;
    }

    public function getNumberPractice($data)
    {
        $record = $this->practiceRepository->findByFilter([
            'week_id' => $data['week_id'],
            'book_id' => $data['book_id']
        ], [
            'order' => " numberPractice DESC "
        ]);

        if (!empty($record->id)) {
            $startNumber = $record->numberPractice + 1;
        } else {
            $startNumber = 1;
        }

        $numberPracticeArray = [];
        for ($i = $startNumber; $i <= $startNumber + 5; $i++) {
            $numberPracticeArray[] = [
                'value' => $i,
                'label' => 'luyện tập ' . $i
            ];
        }

        return $numberPracticeArray;
    }
    public function getNumberPracticeEdit($data)
    {
        $record = $this->practiceRepository->findByFilter([
            'week_id' => $data['week_id'],
            'book_id' => $data['book_id']
        ], [
            'order' => " numberPractice DESC "
        ]);

        if (!empty($record->id)) {
            $startNumber = $record->numberPractice;
        } else {
            $startNumber = 1;
        }

        $numberPracticeArray = [];
        for ($i = $startNumber; $i <= $startNumber + 5; $i++) {
            $numberPracticeArray[] = [
                'value' => $i,
                'label' => 'luyện tập ' . $i
            ];
        }

        return $numberPracticeArray;
    }

    public function store($data)
    {
        if (empty($data['book_id']) || empty($data['week_id']) || empty($data['name'])) {
            return false;
        }

        $sortOrder = $this->practiceRepository->getSortOrder($data['book_id'], $data['week_id']);

        $user = auth()->user();
        $settingAdvance = [
            'background' => $data['background'],
            'color_not_practice' => $data['color_not_practice'],
            'color_done_practice' => $data['color_done_practice'],
        ];
        try {
            $this->practiceRepository->create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'sort_order' => $sortOrder,
                'taptrung' => $data['taptrung'],
                'week_id' => $data['week_id'],
                'book_id' => $data['book_id'],
                'status' => PracticeConstant::ON,
                'practice_id' => PracticeConstant::PREFIX_ID . $data['numberPractice'],
                'numberPractice' => $data['numberPractice'],
                'sort_number' => $data['sort_number'],
                'avatar' => $data['avatar'],
                'setting_advance' => json_encode($settingAdvance)
            ]);

        } catch (\Exception $exception) {
            Log::info([$exception->getMessage()]);
            Log::info([$exception->getLine()]);
        }

        return true;
    }

    public function getDetail($id, $withData = [])
    {
        $lesson = $this->practiceRepository->findByFilter([
            'id' => $id
        ], array_merge($withData, ['week', 'book']));

        $lesson->img_show = $lesson->avatar ? Helper::getCloudFront($lesson->avatar) : null;
        $lesson->img_db = $lesson->avatar;
        $settingAdvance = $lesson->setting_advance ? json_decode($lesson->setting_advance, true) : null;
        $lesson->background = $settingAdvance ? json_decode($settingAdvance['background']) : null;
        $lesson->color_not_practice = $settingAdvance ? json_decode($settingAdvance['color_not_practice']) : null;
        $lesson->color_done_practice = $settingAdvance ? json_decode($settingAdvance['color_done_practice']) : null;

        return $lesson;
    }

    public function update($data)
    {
        $record = $this->practiceRepository->findByFilter([
            'id' => $data['id']
        ]);

        if (empty($record->id)) {
            return [
                'status' => false,
                'messages' => [
                    'id' => 'Bài học không tồn tại.'
                ]
            ];
        } else {
            $settingAdvance = [
              'background' => $data['background'],
              'color_not_practice' => $data['color_not_practice'],
              'color_done_practice' => $data['color_done_practice'],
            ];
            $record->name = $data['name'];
            $record->book_id = $data['book_id'];
            $record->week_id = $data['week_id'];
            $record->taptrung = $data['taptrung'];
            $record->numberPractice = $data['numberPractice'];
            $record->avatar = $data['avatar'];
            $record->setting_advance = $settingAdvance;
            $record->practice_id = PracticeConstant::PREFIX_ID . $data['numberPractice'];

            $record->save();

            return [
                'status' => true
            ];
        }
    }

    public function delete($id)
    {
        QuestionEditor::where('practice_id', $id)->delete();
        Answer::where('practice_id', $id)->delete();
        Question::where('practice_id', $id)->delete();
        Practice::where('id', $id)->delete();
        return [
            'status' => true
        ];
    }

    public function updateVisible($data)
    {
        $this->practiceRepository->updateByFilters([
            'id' => $data['id']
        ], [
            'status' => $data['status'] == 'on' ? 'on' : 'off'
        ]);
    }

    public function storeByConditions($filter, $updates)
    {
        $this->practiceRepository->updateByFilters($filter, $updates);

        return true;
    }

    public function getPrevById($recordTo)
    {
        return $this->practiceRepository->getPrevById($recordTo);
    }
    public function getNextById($recordTo)
    {
        return $this->practiceRepository->getNextById($recordTo);
    }

    public function updateNextPosition($recordFrom, $recordTo, $nextToId)
    {
        if (!$nextToId) {
            $this->practiceRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => microtime(true) * 10000
            ]);
        } else {
            $this->practiceRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $nextToId->sort_number) / 2
            ]);
        }
    }
    public function updatePrevPosition($recordFrom, $recordTo, $prevToId)
    {
        if (!$prevToId) {
            $this->practiceRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => $recordTo->sort_number - 1
            ]);
        } else {
            $this->practiceRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $prevToId->sort_number) / 2
            ]);
        }
    }
    public function dragPosition($fromId, $toId)
    {
        $recordFrom =$this->practiceRepository->findOrFail($fromId);
        $recordTo =$this->practiceRepository->findOrFail($toId);
        $sortNumber = $recordTo->sort_number;

        if ($recordFrom->sort_number > $recordTo->sort_number) {
            $middleRecords = $this->practiceRepository->getMiddleRecords($recordTo, $recordFrom);
            $recordFrom->sort_number = $sortNumber;
            $recordFrom->save();
            $sortNumber += 1000;
            $recordTo->sort_number = $sortNumber;
            $recordTo->save();
            foreach ($middleRecords as $record) {
                $sortNumber += 1000;
                $record->sort_number = $sortNumber;
                $record->save();
            }
        }else {
            $middleRecords = $this->practiceRepository->getMiddleRecords($recordFrom, $recordTo);
            $recordFrom->sort_number = $sortNumber;
            $recordFrom->save();
            $sortNumber -= 1000;
            $recordTo->sort_number = $sortNumber;
            $recordTo->save();
            foreach ($middleRecords as $record) {
                $sortNumber -= 1000;
                $record->sort_number = $sortNumber;
                $record->save();
            }
        }
    }

    public function resetPosition($params)
    {
        $listPractices = $this->practiceRepository->getListByFilters([
            'book_id' => $params['book_id'],
            'week_id' => $params['week_id'],
            'sort_by' => 'sort_number',
            'sort_type' => 'ASC'
        ], true);
        $sortNumber = microtime(true) * 10000;
        foreach ($listPractices as $practice) {
            $sortNumber += 1000;
            $practice->sort_number = $sortNumber;
            $practice->save();
        }
    }
}

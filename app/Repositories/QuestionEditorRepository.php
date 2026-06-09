<?php

namespace App\Repositories;

use App\Models\QuestionEditor;

class QuestionEditorRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return QuestionEditor::class;
    }

    public function getListByFilters($filters, $options = [], $getAll = false)
    {
        $query = $this->model->query();
        $query = $query->with(['templateQuestion']);

        if (!empty($filters['practice_id'])) {
            $query->where('question_editor.practice_id', $filters['practice_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('question_editor.title', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($options['orders'])) {
            foreach ($options['orders'] as $key => $type) {
                $query->orderBy($key, $type);
            }
        }

        $query = $query->orderBy('sort_number', 'ASC');


        if (empty($getAll)) {
            return $query->paginate($filters['pageSize']);
        } else {
            return $query->get();
        }
    }

    public function getPrevById($recordTo)
    {
        return $this->model->where('sort_number', '<', $recordTo->sort_number)
            ->where('book_id', $recordTo->book_id)
            ->where('week_id', $recordTo->week_id)
            ->where('practice_id', $recordTo->practice_id)
            ->orderBy('sort_number', 'desc')
            ->first();
    }

    public function getNextById($recordTo)
    {
        return $this->model->where('sort_number', '>', $recordTo->sort_number)
            ->where('book_id', $recordTo->book_id)
            ->where('week_id', $recordTo->week_id)
            ->where('practice_id', $recordTo->practice_id)
            ->orderBy('sort_number', 'asc')
            ->first();
    }

    public function getMiddleRecords($smallRecord, $bigRecord)
    {
        return $this->where('book_id', $smallRecord['book_id'])
            ->where('week_id', $smallRecord['week_id'])
            ->where('practice_id', $smallRecord['practice_id'])
            ->whereNotIn('id', [$smallRecord['id'], $bigRecord['id']])
            ->where('sort_number', '>', $smallRecord['sort_number']) //Do sort_number đang bị lỗi trùng nhau
            ->where('sort_number', '<', $bigRecord['sort_number']) //Do sort_number đang bị lỗi trùng nhau
            ->orderBy('sort_number', 'asc')->get();
    }
}

<?php

namespace App\Repositories;

use App\Models\Question;

class QuestionRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Question::class;
    }

    public function getListByFilters($filters)
    {
        $query = $this->model->query();

        if (!empty($filters['practice_id'])) {
            $query->where('question.practice_id', $filters['practice_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('question.name', 'like', '%' . $filters['search'] . '%');
        }

        $query = $query->orderBy('sort_number', 'ASC');

        return $query->paginate(self::pagingItem);
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
}

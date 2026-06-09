<?php

namespace App\Repositories;

use App\Models\UserTask;

class UserTaskRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return UserTask::class;
    }

    public function getUserTask($params)
    {
        $query = $this->model;
        if (!empty($params['user_id'])) {
            $query = $query->where('user_id', $params['user_id']);
        }

        if (!empty($params['is_completed'])) {
            $query = $query->where('is_completed', $params['is_completed']);
        }

        if (!empty($params['day'])) {
            $query = $query->where('day', $params['day']);
        }

        if (!empty($params['type'])) {
            $type = $params['type'];
            $query = $query->with(['task' => function ($query) use ($type) {
                $query->where('type', $type);
            }]);
        } else {
            $query->with('task');
        }

        return $query->get();
    }

    public function getDetail($userId, $day, $type)
    {
        $query = $this->model->selectRaw("
                user_tasks.id,
                user_tasks.total,
                tasks.id as task_id,
                tasks.total as task_total
            ")
            ->where('user_tasks.user_id', $userId)
            ->where('user_tasks.day', $day)
            ->join('tasks', function ($q) use ($type) {
                $q->on('tasks.id', '=', 'user_tasks.task_id')
                    ->where('tasks.type', $type);
            });

        return $query->first();
    }
}

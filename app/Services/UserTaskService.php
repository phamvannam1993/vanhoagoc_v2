<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use App\Repositories\TotalUserTaskRepository;
use App\Repositories\UserTaskRepository;

class UserTaskService
{
    private $userTaskRepository;
    private $taskRepository;
    private $totalUserTaskRepository;

    public function __construct(
        UserTaskRepository $userTaskRepository,
        TaskRepository $taskRepository,
        TotalUserTaskRepository $totalUserTaskRepository
    ) {
        $this->userTaskRepository = $userTaskRepository;
        $this->taskRepository = $taskRepository;
        $this->totalUserTaskRepository = $totalUserTaskRepository;
    }

    public function getTask($params)
    {
        $userTasks = $this->userTaskRepository->getUserTask($params);
        if (count($userTasks) > 0) {
            return $userTasks;
        }

        $tasks = $this->taskRepository->all();
        if (count($tasks) > 0) {
            $datas = [];
            foreach ($tasks as $task) {
                $datas[] = array_merge($params, [
                    'task_id' => $task->id
                ]);
            }

            if (count($datas) > 0) {
                $this->userTaskRepository->insert($datas);

                $userTasks = $this->userTaskRepository->getUserTask($params);
                return $userTasks;
            }
        }
    }

    public function save($params)
    {
        $userId = $params['user_id'];
        $userTask = $this->userTaskRepository->getDetail($userId, date('Y-m-d'), $params['type']);

        if (empty($userTask->id)) {
            return [
                'success' => false,
                'message' => 'Không tồn tại user task.'
            ];
        } else {
            $total = $userTask->total + $params['total'];

            $dataUpdate['total'] = $total;

            if ($total > $userTask->task_total) {
                $dataUpdate['total'] = $userTask->task_total;
            }

            if ($total >= $userTask->task_total) {
                $dataUpdate['is_completed'] = true;
            }

            if ($userTask->task_total == 20) {
                $this->taskRepository->updateByFilters([
                    'id' => $userTask->task_id
                ], [
                    'total' => 1
                ]);
            }

            $this->userTaskRepository->updateByFilters(['id' => $userTask->id], $dataUpdate);

            return [
                'success' => true,
                'message' => 'Cập nhật thành công'
            ];
        }
    }

    public function getTotalCompleted($params)
    {
        $userId = $params['user_id'];
        $day = date('Y-m-d');

        $record = $this->totalUserTaskRepository->first([
            'user_id' => $userId,
            'day' => $day
        ]);

        return empty($record->total) ? 0 : $record->total;
    }
}

<?php

namespace App\Services;

use App\Enums\CommentConstant;
use App\Helpers\Helper;
use App\Repositories\CommentRepository;
use App\Repositories\AppRepository;
use App\Repositories\UserRepository;

class CommentService
{
    private $commentRepository;
    private $appRepository;
    private $userRepository;

    public function __construct(
        CommentRepository $commentRepository,
        AppRepository $appRepository,
        UserRepository $userRepository,
    ) {
        $this->commentRepository = $commentRepository;
        $this->appRepository = $appRepository;
        $this->userRepository = $userRepository;
    }

    public function saveComment($data)
    {
        try {
            $this->commentRepository->create($data);
            return response()->json(["success" => true]);
        } catch (\Exception $ex) {
            return response()->json(["success" => false, "message" => $ex->getMessage()]);
        }
    }

    public function getList($data)
    {

        $list = $this->commentRepository->searchByFilters($data);

        foreach ($list as $item) {
            $item->app = $this->appRepository->find($item->app_id, ['id', 'name', 'img']);
            $item->user = $this->userRepository->find($item->user_id, ['id', 'name', 'img', 'email']);
            $item->img = $item->img ? Helper::getCloudFront($item->img) : null;
            $item->link = $item->link ? urldecode($item->link) : null;
            $item->created_at_formatted = $item->created_at
                ? $item->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i')
                : null;

            if ($item->user && $item->user->img) {
                $item->user->img = $item->user->img ? Helper::getCloudFront($item->user->img) : null;
            }
        }

        return $list;
    }

    public function getOneBy($params = [])
    {
        return $this->commentRepository->first($params);
    }

    public function delete($id)
    {
        $record = $this->commentRepository->first([
            'id' => $id
        ]);

        if ($record) {
            $this->commentRepository->deleteByFilter([
                'id' => $id
            ]);

            return [
                'status' => true
            ];
        }

        return false;
    }

    public function findById($id)
    {
        return $this->commentRepository->findOrFail($id);
    }

    public function update($params)
    {
        $comment = $this->findById(data_get($params, 'id'));

        $comment->status = data_get($params, 'status', CommentConstant::NEW);
        $comment->save();
    }
}

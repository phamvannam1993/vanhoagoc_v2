<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Services\CommentService;
use App\Services\AppService;
use Illuminate\Http\Request;

class CommentController extends BaseModuleController
{
    public function saveComment(Request $request, CommentService $commentService, AppService $appService)
    {
        if(!$request["comment"]) {
            return response()->json(["success" => false, "Nội dung comment không được để trống"], 400);
        }
        if(!$request["app_id"]) {
            return response()->json(["success" => false, "App id không được để trống"], 400);
        }
        $app = $appService->findAppById($request["app_id"]);
        if(empty($app)) {
            return response()->json(["success" => false, "App không tồn tại"], 400);
        }

        $checkUser = $commentService->getOneBy(["user_id" => $request["user_id"], "app_id" => $request["app_id"]]);
        $totalUserComment = $app->total_user_comment ? $app->total_user_comment : 0;
        if(!$checkUser) {
            $totalUserComment = $totalUserComment + 1;
        }
        $total_comment = $app->total_comment ? $app->total_comment + 1 : 1;
        $app->update(['total_comment' => $total_comment, 'total_user_comment' => $totalUserComment]);
        $dataComment = [
            'comment' => $request["comment"],
            'user_id' => $request["user_id"],
            'app_id' => $request["app_id"],
            'img' => $request["urlImg"] ?? null,
            'link' => $request["link"] ? urlencode($request["link"]) : null
        ];
        return $commentService->saveComment($dataComment);
    }

    public function getList(Request $request, CommentService $commentService) {
        $list = $commentService->getList($request->all());
        return $list;
    }
}

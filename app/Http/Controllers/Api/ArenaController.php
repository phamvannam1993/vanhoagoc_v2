<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use Illuminate\Http\Request;

class ArenaController extends BaseModuleController
{
    public function getNowTimestamp() {
        $current_time = round(microtime(true) * 1000);
        return response()->json(["success" => true, "message" => "success","timestamp" => $current_time]);
    }
}

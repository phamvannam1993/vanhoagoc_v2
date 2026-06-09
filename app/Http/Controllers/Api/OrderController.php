<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseModuleController;
use App\Models\Order;
use App\Models\ActiveCode;
use Illuminate\Http\Request;

class OrderController extends BaseModuleController
{
   public function postOrder(Request $request) {
        $user_id = $request->user_id;
        $code = $request->code;
        $price = $request->price;
        if($code) {
            $activeCode = ActiveCode::where('code', $code)->where('price', $price)->where('is_used', 0)->first();
            if(!$activeCode) {
                return response()->json(['result' => false, 'message' => 'Mã kích hoạt không tồn tại hoặc đã được sử dụng']);
            }
            $activeCode->update(['is_used' => 1]);
        }
        $dataSave = [
            'user_id' => $user_id,
            'namesubject' => $request->namesubject,
            'price' =>  $request->price
        ];
        if($request->namesubject) {
            Order::create($dataSave);
        }
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
   }

   public function getOrder(Request $request) {
        $user_id = $request->user_id;
        $orders = Order::where('user_id', $user_id)->get();
        return response()->json(['success' => true, 'data' => $orders]);
   }

   public function checkActiveCode(Request $request) {
        $code = $request->code;
        $price = $request->price;
        $activeCode = ActiveCode::where('code', $code)->where('price', $price)->where('is_used', 0)->first();
        if($activeCode) {
            return response()->json(['result' => true, 'message' => 'Kiểm tra mã thành công']);
        } else {
            return response()->json(['result' => false, 'message' => 'Mã kích hoạt không tồn tại hoặc đã được sử dụng']);
        }
   }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TeachingController extends Controller
{
    // Trang trung gian "Giao bài" — chọn Lớp rồi vào màn giao bài
    public function assign(Request $request): Response
    {
        return $this->render('Teaching/Assign', $request);
    }

    // Trang trung gian "Kết quả" — chọn Lớp rồi xem kết quả học tập
    public function result(Request $request): Response
    {
        return $this->render('Teaching/Result', $request);
    }

    private function render(string $component, Request $request): Response
    {
        $user = Auth::user();
        // Giáo viên chỉ thuộc một App → tự suy ra app_id (giống ClassController@index).
        // Admin/Giám đốc: để trống, ClassPicker sẽ hiển thị bộ chọn Đơn vị.
        $appId = $user->userType?->type === UserType::TYPE_TEACHER
            ? $user->teacherApp->first()?->app_id
            : '';

        return Inertia::render($component, [
            'appId' => $appId,
        ]);
    }
}

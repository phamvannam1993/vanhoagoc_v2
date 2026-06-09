<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class UsersExport implements FromView
{
    protected $classId;

    public function __construct($classId)
    {
        $this->classId = $classId;
    }

    public function view(): View
    {

        $students = User::whereHas('classes', function ($query) {
            $query->where('class_id', $this->classId);
        })->get();

        return view('exports.students', [
            'students' => $students
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\AssignmentStudent;
use App\Models\ExerciseAssignment;
use App\Models\ExerciseItem;
use App\Models\UserClass;
use Illuminate\Support\Facades\Log;

class ExerciseAssignmentService
{
    /**
     * Assign exercise items to an individual student
     */
    public function assignToStudent(array $data): array
    {
        $practiceId = $data['practice_id'];
        $studentId = $data['student_id'];
        $itemIds = $data['exercise_item_ids'];
        $classId = $data['class_id'] ?? null;

        // Auto-detect class_id from student if not provided
        if (!$classId) {
            $userClass = UserClass::where('user_id', $studentId)->first();
            if ($userClass && $userClass->class_id) {
                $classId = $userClass->class_id;
            }
        }

        // Validate: Check for duplicate assignments
        $duplicateCount = AssignmentStudent::where('student_id', $studentId)
            ->join('exercise_assignments', 'assignment_students.exercise_assignment_id', '=', 'exercise_assignments.id')
            ->whereIn('exercise_assignments.exercise_item_id', $itemIds)
            ->count();

        if ($duplicateCount > 0) {
            return [
                'status' => false,
                'message' => 'Học sinh này đã được giao một số bài tập con này rồi. Vui lòng kiểm tra lại!'
            ];
        }

        // Validate: Check if student has assignment for ANY item in this practice
        $allItemsInPractice = ExerciseItem::where('practice_id', $practiceId)->pluck('id')->toArray();
        $existingCount = AssignmentStudent::where('student_id', $studentId)
            ->join('exercise_assignments', 'assignment_students.exercise_assignment_id', '=', 'exercise_assignments.id')
            ->whereIn('exercise_assignments.exercise_item_id', $allItemsInPractice)
            ->count();

        if ($existingCount > 0) {
            return [
                'status' => false,
                'message' => 'Học sinh này đã được giao 1 bài tập con trong bài tập này rồi. Mỗi học sinh chỉ được giao 1 bài con duy nhất!'
            ];
        }

        // Create assignments
        $fromDate = $data['checkedTime'] ? $data['from'] : date('Y-m-d', strtotime('+1 year'));

        foreach ($itemIds as $itemId) {
            $assignment = ExerciseAssignment::create([
                'exercise_item_id' => $itemId,
                'class_code' => "class_$classId",
                'due_date' => $fromDate,
                'note' => $data['checkedNonTime'] ? "Vô thời hạn" : "",
                'status' => 'active',
            ]);

            AssignmentStudent::firstOrCreate([
                'exercise_assignment_id' => $assignment->id,
                'student_id' => $studentId,
            ], [
                'status' => 'pending',
            ]);
        }

        Log::info('Exercise items assigned to individual student', [
            'practice_id' => $practiceId,
            'student_id' => $studentId,
            'class_id' => $classId,
            'item_count' => count($itemIds),
        ]);

        return [
            'status' => true,
            'message' => 'Giao bài tập con thành công'
        ];
    }

    /**
     * Assign exercise items to entire class
     */
    public function assignToClass(array $data): array
    {
        $practiceId = $data['practice_id'];
        $classId = $data['class_id'];
        $itemIds = $data['exercise_item_ids'];

        // Get all students in the class
        $studentIds = UserClass::where('users_classes.class_id', $classId)
            ->join('users', 'users_classes.user_id', '=', 'users.id')
            ->where('users.user_type_id', 3)  // 3 = student
            ->pluck('users_classes.user_id')
            ->toArray();

        if (empty($studentIds)) {
            return [
                'status' => false,
                'message' => 'Lớp không có học sinh'
            ];
        }

        // Create assignments for all students
        $fromDate = $data['checkedTime'] ? $data['from'] : date('Y-m-d', strtotime('+1 year'));

        foreach ($itemIds as $itemId) {
            $assignment = ExerciseAssignment::create([
                'exercise_item_id' => $itemId,
                'class_code' => "class_$classId",
                'due_date' => $fromDate,
                'note' => $data['checkedNonTime'] ? "Vô thời hạn" : "",
                'status' => 'active',
            ]);

            foreach ($studentIds as $studentId) {
                AssignmentStudent::firstOrCreate([
                    'exercise_assignment_id' => $assignment->id,
                    'student_id' => $studentId,
                ], [
                    'status' => 'pending',
                ]);
            }
        }

        Log::info('Exercise items assigned to class', [
            'practice_id' => $practiceId,
            'class_id' => $classId,
            'student_count' => count($studentIds),
            'item_count' => count($itemIds),
        ]);

        return [
            'status' => true,
            'message' => 'Giao bài tập con cho cả lớp thành công'
        ];
    }

    /**
     * Withdraw assignment from individual student
     */
    public function withdrawFromStudent(int $exerciseItemId, int $studentId): array
    {
        $assignments = ExerciseAssignment::where('exercise_item_id', $exerciseItemId)->pluck('id')->toArray();

        if (empty($assignments)) {
            return [
                'status' => false,
                'message' => 'Không tìm thấy bài tập con để hủy giao'
            ];
        }

        AssignmentStudent::whereIn('exercise_assignment_id', $assignments)
            ->where('student_id', $studentId)
            ->delete();

        Log::info('Exercise item withdrawn from student', [
            'exercise_item_id' => $exerciseItemId,
            'student_id' => $studentId,
        ]);

        return [
            'status' => true,
            'message' => 'Hủy giao bài tập con thành công'
        ];
    }

    /**
     * Withdraw assignment from entire class
     */
    public function withdrawFromClass(int $exerciseItemId, int $classId): array
    {
        $assignments = ExerciseAssignment::where('exercise_item_id', $exerciseItemId)->pluck('id')->toArray();

        if (empty($assignments)) {
            return [
                'status' => false,
                'message' => 'Không tìm thấy bài tập con để hủy giao'
            ];
        }

        // Get all students in class
        $studentIds = UserClass::where('users_classes.class_id', $classId)
            ->join('users', 'users_classes.user_id', '=', 'users.id')
            ->where('users.user_type_id', 3)
            ->pluck('users_classes.user_id')
            ->toArray();

        if (!empty($studentIds)) {
            AssignmentStudent::whereIn('exercise_assignment_id', $assignments)
                ->whereIn('student_id', $studentIds)
                ->delete();

            Log::info('Exercise item withdrawn from class', [
                'exercise_item_id' => $exerciseItemId,
                'class_id' => $classId,
                'student_count' => count($studentIds),
            ]);
        }

        return [
            'status' => true,
            'message' => 'Hủy giao bài tập con cho cả lớp thành công'
        ];
    }
}

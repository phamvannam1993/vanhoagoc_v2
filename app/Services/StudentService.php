<?php

namespace App\Services;

use App\Models\Point;
use App\Models\PracticeClass;
use App\Models\UserClass;
use App\Models\UserRankPractice;
use App\Repositories\PracticeClassRepository;
use App\Repositories\PointRepository;
use App\Repositories\UserClassRepository;
use App\Repositories\UserRepository;

class StudentService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserClassRepository $userClassRepository,
        private PracticeClassRepository $practiceClassRepository,
        private PointRepository $pointRepository,
        private UserService $userService,
        private RoleService $roleService
    )
    {}

    public function getFreePractice($params = []) {
        $classId = $params['class_id'];
        $userId = $params['user_id'];
        $params = [
            'user_id' => $userId,
            'class_id' => $classId,
            'is_assign' => 2,
            'type' => 1
        ];
        $totalUser = UserClass::where('class_id', $classId)->count();
        $practicePoint = Point::where($params)->with([
            'pointDetails',
            'practice.week.book',
            'practice.userRankPractices' => function($q) use($userId, $classId){
                $q->where('user_id', $userId)->where('class_id', $classId);
            }
        ])->orderBy('star_count', 'DESC')->get();
        $practice_ids = [];
        $data1 = [];
        foreach($practicePoint as $item) {
            $seconds = $item->time;

            $seconds = (int)$seconds;

            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;

            if ($hours > 0) {
                $formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
            } else {
                $formatted = sprintf("%02d:%02d", $minutes, $secs);
            }
            $item->time_text = $formatted;
            $item->day_create = $item->created_at;
            if($item->practice_id > 0 && !in_array($item->practice_id, $practice_ids)) {
                $practice_ids[] = $item->practice_id;
                $pointDetails = $item->pointDetails;
                $totalPoint = count($pointDetails) *10;
                $totalCorrect = 0;
                foreach($pointDetails as $pointDetail) {
                    if($pointDetail->star_count == 1) {
                        $totalCorrect = $totalCorrect + 1;
                    }
                }
                $item->totalCorrect = $totalCorrect;
                $item->point_text = ($totalCorrect*10).'/'.$totalPoint;
                $data1[$item->practice_id] = $item;
            }
        }

        $params['type'] = 2;
        $theoreticalPoint = Point::where($params)->orderBy('star_count', 'DESC')->get();

        $practice_ids = [];
        $data2 = [];
        foreach($theoreticalPoint as $item) {
            $item->time_text = "";
            $item->day_create = $item->created_at;
            $item->totalCorrect = $item->star_count/10;
            $item->point_text = $item->star_count;
            if($item->practice_id > 0 && !in_array($item->practice_id, $practice_ids)) {
                $practice_ids[] = $item->practice_id;
                $data2[$item->practice_id] = $item;
            }
        }

        $data_ghep = [];
        $data = [];
        // Lấy tất cả các key từ cả 2 mảng
        $all_keys = array_unique(array_merge(array_keys($data1), array_keys($data2)));
        foreach ($all_keys as $key) {
            $has1 = array_key_exists($key, $data1);
            $has2 = array_key_exists($key, $data2);
            if ($has1 && $has2) {
                $data_ghep[$key] = [$data1[$key], $data2[$key]];
            } elseif ($has1) {
                $data_ghep[$key] = [$data1[$key]];
            } elseif ($has2) {
                $data_ghep[$key] = [$data2[$key]];
            }
        }
        foreach($data_ghep as $key => $items) {
            $dataItem = [
                'name' => '',
                'id' => '',
                'rank' => 'Chưa xếp hạng',
                'day_create' => '',
                'point_text_1' => '',
                'point_text_2' => '',
                'time_text' => '',
                'total_point' => 0,
            ];

            $totalPoint = 0;

            foreach($items as $item) {
                $practiceId = $item->practice_id;
                $practice = $item->practice;
                if(!empty($practice)) {
                    $week = $practice->week;
                    $book = $week?->book;
                    $dataItem['name'] = ($book ? $book->title.' - ' : '').$week->name.' - '.$practice->name;
                }
                $totalPoint = $item->totalCorrect + $totalPoint;
                $dataItem['day_create'] = $item->day_create;
                if($item->type == 1) {
                    $dataItem['point_text_1'] = $item->point_text;
                    $dataItem['time_text'] = $item->time_text;
                    $dataItem['id'] = $item->id;
                } else {
                    $dataItem['point_text_2'] = $item->totalCorrect*10;
                }
            }
            $dataItem['total_point'] = $totalPoint*10;

            if (!empty($practice)) {
                $userRankPractice = $practice->userRankPractices->first();
            }

            if(!empty($userRankPractice)) {
                $userRankPractice->update(['total_point' => $totalCorrect*10]);
            } else {
                $dataSave = [
                    'user_id' => $userId,
                    'class_id' => $classId,
                    'practice_id' => $practiceId,
                    'total_point' => $totalPoint*10
                ];
                $userRankPractice = UserRankPractice::create($dataSave);
            }
            $userPoint = data_get($userRankPractice, 'total_point');
            $rank = $this->__getTotalRank($userPoint, $classId, $practiceId);
            if($rank) {
                $dataItem['rank'] = $rank.'/'.$totalUser;
            }
            $data[] = $dataItem;
        }
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function assignedTask($params = []) {
        $classId = $params['class_id'];
        $userId = $params['user_id'];
        $totalUser = UserClass::where('class_id', $classId)->count();
        $practiceData = PracticeClass::with([
            'practice',
            'practice.week',
            'practice.practicePoints' => function($q) use($userId, $classId){
                return $q->where('is_assign', 1)
                    ->where('user_id', $userId)
                    ->where('class_id', $classId)
                    ->orderBy('star_count', 'DESC');
            },
            'practice.userRankPractices' => function($q) use($userId, $classId){
                $q->where('user_id', $userId)->where('class_id', $classId);
            },
            'practice.practicePoints.pointDetails',
            'book',
            'week',
            'class',
        ])->where('class_id', $classId)
          ->where(function($q) use($userId) {
              $q->whereNull('student_id')->orWhere('student_id', $userId);
          })->get();

        foreach($practiceData as $item) {
            $week = $item->practice->week;
            $item->name = $item->book->title.' - '.$week->name.' - '.$item->practice->name;
            $status = 0;
            $review_status = 0;
            $params = [
                'practice_id' => $item->practice->id,
                'user_id' => $userId,
                'class_id' => $classId,
                'is_assign' => 1
            ];
            $point = $item->practice->practicePoints->first();
            $dateInput = new \DateTime($item->to);
            $now = new \DateTime();
            if(!empty($point))  {
                $status = 1;
                $review_status = 2;
            } else if (!empty($item->to) && $dateInput < $now) {
                $status = 2;
                $review_status = 2;
            }
            $item->point_text = "";
            $item->status = $status;
            $item->duration = "";

            if (empty($item->to)) {
                $item->duration = "Vô thời hạn";
            }

            if($item->from) {
                $timeFrom = date('d/m/Y', strtotime($item->from));
                $timeTo = date('d/m/Y', strtotime($item->to));
                $item->duration = 'Từ '.$timeFrom.' đến '.$timeTo;
            }
            if(!empty($point)) {
                $seconds = $point->time;
                $seconds = (int)$seconds;

                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;

                if ($hours > 0) {
                    $formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
                } else {
                    $formatted = sprintf("%02d:%02d", $minutes, $secs);
                }
                $item->time_text = $formatted;
                $pointDetails = $point->pointDetails;

                $totalPoint = count($pointDetails) *10;
                $totalCorrect = 0;
                foreach($pointDetails as $pointDetail) {
                    if($pointDetail->star_count == 1) {
                        $totalCorrect = $totalCorrect + 1;
                    }
                }
                if($totalCorrect*1000/$totalPoint >= 50) {
                    $review_status = 1;
                }
                $item->point_text = ($totalCorrect*10).'/'.$totalPoint;
                $item->point_id = $point->id;
                $userRankPractice = $item->practice->userRankPractices->first();
                if(!empty($userRankPractice)) {
                    $userRankPractice->update(['total_point' => $totalCorrect*10]);
                } else {
                    $dataSave = [
                        'user_id' => $userId,
                        'class_id' => $classId,
                        'practice_id' => $item->practice->id,
                        'total_point' => $totalCorrect*10
                    ];
                    $userRankPractice = UserRankPractice::create($dataSave);
                }
                $userPoint = data_get($userRankPractice, 'total_point');
                $rank = $this->__getTotalRank($userPoint, $classId, $item->practice->id);
                $item->rank = 'Chưa xếp hạng';
                if($rank) {
                    $item->rank = $rank.'/'.$totalUser;
                }
            }
            $item->review_status = $review_status;
        }
        return response()->json([
            'status' => true,
            'data' => $practiceData
        ]);
    }

    public function getTotalRank($userId, $classId, $practiceId) {
        $userPoint = UserRankPractice::where('user_id', $userId)->where('class_id', $classId)->where('practice_id', $practiceId)->value('total_point');
        if ($userPoint === null) {
            $rank = null; // Không tìm thấy user
        } else {
            // Đếm số người có point cao hơn
            $rank = UserRankPractice::where('class_id', $classId)->where('practice_id', $practiceId)
                ->where('total_point', '>', $userPoint)
                ->count() + 1;
        }
        return $rank;
    }

    public function createStudent($params)
    {
        auth()->login($this->userRepository->findOrFail(env('MASTER_USER_ID')));

        $user = $this->userService->store($params);

        if ($user) {
            $this->userClassRepository->where(['user_id' => $user->id])->delete();
            $this->userClassRepository->create([
                'user_id' => $user->id,
                'class_id' => $params['class_id'] ?? null,
                'app_id' => $params['app_id']
            ]);

            $this->roleService->assignRoleUser([
                'user_id' => $user->id,
                'app_id' => 'p_' . $params['app_id'],
                'type_id' => $params['user_type_id'],
            ]);
            $user->email = !empty($params['email']) ? $params['email'] : 'user_'.$user->id;
            $user->save();
        } else {
            return;
        }

        return $user;
    }

    private function __getTotalRank($userPoint, $classId, $practiceId) {
        if ($userPoint === null) {
            $rank = null; // Không tìm thấy user
        } else {
            // Đếm số người có point cao hơn
            $rank = UserRankPractice::where('class_id', $classId)->where('practice_id', $practiceId)
                ->where('total_point', '>', $userPoint)
                ->count() + 1;
        }
        return $rank;
    }

    //for api
     public function getAssignment($params = []) {
        $userId = $params['user_id'];
        $practiceData = $this->practiceClassRepository->getListByUserId($userId, [
            'practice',
            'practice.practicePoints' => function($q) use($userId){
                return $q->where('is_assign', 1)
                    ->where('user_id', $userId)
                    ->orderBy('star_count', 'DESC');
            },
            'practice.practicePoints.pointDetails',
        ]);

        $result = [];
        foreach($practiceData as $item) {
            $practice = data_get($item, 'practice');
            if (empty($practice)) {
                continue;
            }

            $status = 0;

            $point = $practice->practicePoints->first();

            $totalCorrect = 0;
            $formatted = 'Vô thời hạn';
            $totalPoint = 0;
            if(!empty($point)) {
                $seconds = $point->time;
                $seconds = (int)$seconds;

                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;

                if ($hours > 0) {
                    $formatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $secs);
                } else {
                    $formatted = sprintf("%02d:%02d", $minutes, $secs);
                }
                $pointDetails = $point->pointDetails;

                $totalPoint = count($pointDetails) *10;
                foreach($pointDetails as $pointDetail) {
                    if($pointDetail->star_count == 1) {
                        $totalCorrect++;
                    }
                }
            }

            $result[] = [
                'id' => $item->id,
                'practice_id' => $practice->id,
                'name' => $practice->name,
                'time_text' => $formatted,
                'status' => $status,
                'status_label' => $item->getStatusLabel(),
                'point_id' => $item->point_id,
                'total_correct' => $totalCorrect,
                'total_point' => $totalPoint,
                'answer_correct_percent' => ($totalPoint > 0) ? ($totalCorrect*100/$totalPoint) : 0,
            ];
        }
        return $result;
    }

    public function getAssignmentDetail($params) {
        $points = $this->pointRepository->findByFilter([
            'practice_id' => $params['practice_id'],
            'user_id' => $params['user_id']
        ], ['pointDetails.questionEditor']);

        $pointDetails = data_get($points, 'pointDetails', []);
        $result = [];
        foreach ($pointDetails as $pointDetail) {
            if (empty($pointDetail->time)) {
                $result_text = 'Chưa thực hiện';
            } else {
                $result_text = $pointDetail->star_count == 1 ? 'Chính xác' : 'Chưa chính xác';
            }
            $result[] = [
                'id' => $pointDetail->id,
                'title' => data_get($pointDetail, 'questionEditor.title'),
                'star_count' => $pointDetail->star_count,
                'result_text' => $result_text,
                'time' => $pointDetail->time
            ];
        }
        return $result;
    }
}

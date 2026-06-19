<?php

namespace App\Services;

use App\Enums\PointConstant;
use App\Enums\PointDayConstant;
use App\Repositories\PointDayRepository;
use App\Repositories\PointDetailRepository;
use App\Repositories\PointRepository;
use App\Repositories\PracticePointDayRepository;
use App\Repositories\UserInfoRepository;
use App\Repositories\UserRepository;
use App\Models\Point;
use App\Models\UserClass;
use App\Models\PointDetail;
use App\Models\Week;
use App\Models\PracticeClass;
use App\Services\StudentService;
use App\Repositories\UserTotalPointRepository;
use Carbon\Carbon;

class PointService
{
    private $practicePointDayRepository;
    private $pointRepository;
    private $pointDetailRepository;
    private $userTotalPointRepository;
    private $userInfoRepository;
    private $userRepository;
    private $pointDayRepository;
    protected $studentService;

    public function __construct(
        PracticePointDayRepository $practicePointDayRepository,
        PointRepository $pointRepository,
        PointDetailRepository $pointDetailRepository,
        UserTotalPointRepository $userTotalPointRepository,
        UserInfoRepository $userInfoRepository,
        UserRepository $userRepository,
        StudentService $studentService,
        PointDayRepository $pointDayRepository
    ) {
        $this->practicePointDayRepository = $practicePointDayRepository;
        $this->pointRepository = $pointRepository;
        $this->pointDetailRepository = $pointDetailRepository;
        $this->userTotalPointRepository = $userTotalPointRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->userRepository = $userRepository;
        $this->pointDayRepository = $pointDayRepository;
        $this->studentService = $studentService;
    }

    public function save($data, $type)
    {
        $day = date('Y-m-d');
        $week = date('W');
        $data['type'] = $type;
        $userId = $data['user_id'];
        // create point record
        $pointValue = $this->convertDataPoint($data);

        $pointRecord = $this->pointRepository->create($pointValue);

        if ($data['type'] == PointConstant::POINT_PRACTICE) {
            // create point detail records
            if (!empty($data['details'])) {
                $detailSaves = [];

                foreach ($data['details'] as $detail) {
                    $detailSaves[] =  $this->convertDataDetail($detail, $pointRecord->id);
                }

                if (count($detailSaves) > 0) {
                    $this->pointDetailRepository->insert($detailSaves);
                }
            }
        } else {
            $user = $this->userRepository->first(['id' => $userId]);
            if (!empty($user->id)) {
                $point_knowledge = $user->point_knowledge +  $data['point_knowledge'];
                $point_deepview = $user->point_deepview +  $data['point_deepview'];
                $this->userRepository->updateByFilters([
                    'id' => $userId
                ], [
                    'point_knowledge' => $point_knowledge,
                    'point_deepview' => $point_deepview
                ]);
            }
        }

        // register practice point
        $practicePointDay = $this->practicePointDayRepository->first([
            'user_id' => $userId,
            'week' => $week,
            'day' => $day
        ]);

        if (empty($practicePointDay->id)) {
            $inits = $this->getListDay($day, $week, $userId, $data['star_count']);
            $this->practicePointDayRepository->insert($inits);

            $total = $data['star_count'];
        } else {
            $total = $practicePointDay->total + $data['star_count'];

            $this->practicePointDayRepository->updateByFilters([
                'id' => $practicePointDay->id
            ], [
                'total' => $total
            ]);
        }

        $this->updateUsertotalPoint($userId, $data);
        $this->updateUserInfo($userId, $total);
        if(isset($data['class_id']) && !empty($data['class_id'])) {
            $params = [
                'user_id' => $userId,
                'class_id' => $data['class_id']
            ];
            $this->studentService->getFreePractice($params);
            $this->studentService->assignedTask($params);
        }
    }

    public function updateUserInfo($userId, $total)
    {
        $record = $this->userInfoRepository->first([
            'user_id' => $userId
        ]);

        if (empty($record->id)) {
            $this->userInfoRepository->create([
                'user_id' => $userId,
                'total_record_practice' => $total,
                'total_practice' => $total,
                'day_record_practice' => date('Y-m-d H:i:s')
            ]);
        } else {
            $dataUpdate = [
                'total_practice' => $total,
            ];
            if ($record->total_record_practice < $total) {
                $dataUpdate['total_record_practice'] = $total;
                $dataUpdate['day_record_practice'] = date('Y-m-d H:i:s');
            }

            $this->userInfoRepository->updateByFilters([
                'id' => $record->id
            ], $dataUpdate);
        }
    }

    public function updateUsertotalPoint($userId, $data)
    {
        // special type
        $typeRecord = $this->userTotalPointRepository->first([
            'type' => $data['type'],
            'user_id' => $userId
        ]);

        if (empty($typeRecord->id)) {
            $this->userTotalPointRepository->create([
                'total_time' => $data['time'],
                'total_star_count' => $data['star_count'],
                'user_id' => $data['user_id'],
                'type' => $data['type'],
            ]);
        } else {
            $this->userTotalPointRepository->updateByFilters([
                'id' => $typeRecord->id
            ], [
                'total_time' => $typeRecord->total_time + $data['time'],
                'total_star_count' => $typeRecord->total_star_count + $data['star_count'],
            ]);
        }

        // summary
        $summaryRecord = $this->userTotalPointRepository->first([
            'type' => PointConstant::POINT_SUM,
            'user_id' => $userId
        ]);

        if (empty($summaryRecord->id)) {
            $this->userTotalPointRepository->create([
                'total_time' => $data['time'],
                'total_star_count' => $data['star_count'],
                'user_id' => $data['user_id'],
                'type' => PointConstant::POINT_SUM,
            ]);
        } else {
            $this->userTotalPointRepository->updateByFilters([
                'id' => $summaryRecord->id
            ], [
                'total_time' => $summaryRecord->total_time + $data['time'],
                'total_star_count' => $summaryRecord->total_star_count + $data['star_count'],
            ]);
        }
    }

    public function getListDay($today, $week, $userId, $total)
    {
        $datas = [];
        $strtotime = strtotime('monday this week');
        for ($i = 0; $i < 7; $i++) {
            $day = date('Y-m-d', $strtotime);
            $data = [
                'user_id' => $userId,
                'total' => 0,
                'day' => $day,
                'week' => $week,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            if ($day == $today) {
                $data['total'] =  $total;
            }
            array_push($datas, $data);
            $strtotime = strtotime('+1 day', $strtotime);
        }
        return $datas;
    }

    public function convertDataDetail($request, $id)
    {
        $data = [
            'question_id' => $request['question_id'],
            'answer' => isset($request['answer']) ? $request['answer'] : '',
            'star_count' =>  isset($request['star_count']) ? $request['star_count'] : '',
            'tem_playable_id' => isset($request['tem_playable_id']) ? $request['tem_playable_id'] : '',
            'answer_times' => isset($request['answer_times']) ? $request['answer_times'] : '',
            'playable' => isset($request['playable']) ? $request['playable'] : '',
            'screen_shot' => isset($request['screen_shot']) ? $request['screen_shot'] : '',
            'time' =>  $request['time'],
            'point_id' => $id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        return $data;
    }

    public function convertDataPoint($request)
    {
        $data = [
            'user_id' => $request['user_id'],
            'class_id' => $request['class_id'] ?? null,
            'practice_id' => isset($request['practice_id_tool']) ? $request['practice_id_tool'] : '',
            'exercise_id' => isset($request['exercise_id']) ? $request['exercise_id'] : '',
            'name' => $request['name'],
            'screen_shot' => isset($request['screen_shot']) ? $request['screen_shot'] : '',
            'namevideo' =>  isset($request['namevideo']) ? $request['namevideo'] : '',
            'book_id' =>  isset($request['book_id']) ? $request['book_id'] : '',
            'star_count' =>  $request['star_count'],
            'time' =>  $request['time'],
            'format' => isset($request['format']) ? $request['format'] : '',
            'type' =>  $request['type'],
            'is_assign' => $request['baitap'] ??  PointConstant::NOT_ASSIGN,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
        return $data;
    }

    public function getListByType($data)

    {
        $list = $this->pointRepository->getListByType($data);

        return $list;
    }

    public function getSummaryPoint($data)
    {
        $summary = $this->pointRepository->getSummary($data);

        return [
            'total_time' => empty($summary->total_time) ? 0 : $summary->total_time,
            'total_star_count' => empty($summary->total_star_count) ? 0 : $summary->total_star_count
        ];
    }

    public function getRank($data)
    {
        $list = $this->userTotalPointRepository->getList($data);

        return $list;
    }

    public function getTotalPoint($params)
    {
        $userId = $params['user_id'];
        $type = $params['type'];

        $total = $this->getTotal($userId, $type);
        if ($type == PointDayConstant::POINT_SIGHT) {

            $userInfo = $this->userInfoRepository->first(['user_id' => $userId]);
            $dataUserInfo = [
                'total_sight' => $total,
                'user_id' => $userId
            ];
            if (!empty($userInfo->id)) {
                if ($userInfo->total_record_sight < $total) {
                    $dataUserInfo['total_record_sight'] = $total;
                    $dataUserInfo['day_record_sight'] = date('Y-m-d H:i:s');
                }
                $this->userInfoRepository->updateByFilters(['id' => $userInfo->id], $dataUserInfo);
            } else {
                $dataUserInfo['total_record_sight'] = $total;
                $dataUserInfo['day_record_sight'] = date('Y-m-d H:i:s');
                $this->userInfoRepository->create($dataUserInfo);
            }
        }

        return $total;
    }

    public function getTotal($userId, $type)
    {
        $currentDay = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('yesterday'));

        $params = [];
        $pointDay = $this->pointDayRepository->first([
            'user_id' => $userId,
            'day' => $currentDay,
            'type' => $type,
        ]);

        if (!empty($pointDay->id)) {
            return $pointDay->total;
        } else {
            $data = [
                'user_id' => $userId,
                'type' => $type,
                'day' => $currentDay
            ];

            if ($type == PointDayConstant::POINT_SIGHT) {
                $data['total'] = 1;
                $yesterday = $this->pointDayRepository->first([
                    'user_id' => $userId,
                    'day' => $currentDay,
                    'type' => $type,
                ]);

                if (!empty($yesterday->id)) {
                    $data['total'] = $yesterday->total + 1;
                }
            } else {
                $data['total'] = PointDayConstant::TOTAL_FREE;
            }

            $result = $this->pointDayRepository->create($data);

            return $result->total;
        }
    }

    public function subTotalPoint($userId, $type)
    {
        $currentDay = date('Y-m-d');
        $params = [
            'user_id' => $userId,
            'day' => $currentDay,
            'type' => $type
        ];

        $pointDay = $this->pointDayRepository->first($params);

        if (!empty($pointDay->total)) {
            $dataUpdate = [
                'total' => $pointDay->total - 1
            ];
            return  $this->pointDayRepository->updateByFilters(['id' => $pointDay->id], $dataUpdate);
        }

        return true;
    }

    public function getPracticePointDay($data)
    {
        $userId = $data['user_id'];
        $day = date('Y-m-d');
        $week = date('W');

        $filters = [
            'user_id' => $userId,
            'week' => $week
        ];

        $result = $this->practicePointDayRepository->get($filters);

        if (count($result) == 0) {
            $dataSaves = $this->getListDay($day, $week, $userId, 0);

            $this->practicePointDayRepository->insert($dataSaves);
            $result = $this->practicePointDayRepository->get($filters);
        }

        return $result;
    }

    public function assignedTask($classId, $userId = 0) {
        $params = [
            'class_id' => $classId
        ];
        if($userId > 0) {
            $params = [
                'user_id' => $userId
            ];
        }
        $practiceData = PracticeClass::with(['practice', 'book', 'week', 'class.user'])
            ->where($params)
            ->where(function($q) use($userId) {
                if ($userId > 0) {
                    $q->whereNull('student_id')->orWhere('student_id', $userId);
                }
            })
            ->orderBy('created_at', 'DESC')->get();

        // Pre-aggregate submissions per (class_id, practice_id)
        $pairs = $practiceData->map(fn($i) => [data_get($i, 'class.id'), data_get($i, 'practice.id')])
            ->filter(fn($p) => $p[0] && $p[1])->values();
        $classIds = $pairs->pluck(0)->unique()->values()->toArray();
        $practiceIds = $pairs->pluck(1)->unique()->values()->toArray();

        $pointAgg = [];
        if (!empty($classIds) && !empty($practiceIds)) {
            $rows = Point::selectRaw('class_id, practice_id, COUNT(DISTINCT user_id) AS submitted, AVG(star_count) AS avg_point')
                ->whereIn('class_id', $classIds)
                ->whereIn('practice_id', $practiceIds)
                ->where('is_assign', 1)
                ->groupBy('class_id', 'practice_id')
                ->get();
            foreach ($rows as $r) {
                $pointAgg[$r->class_id . '_' . $r->practice_id] = [
                    'submitted' => (int)$r->submitted,
                    'avg_point' => round((float)$r->avg_point, 1),
                ];
            }
        }

        $classTotals = [];
        if (!empty($classIds)) {
            $classTotals = UserClass::whereIn('class_id', $classIds)
                ->selectRaw('class_id, COUNT(DISTINCT user_id) AS total')
                ->groupBy('class_id')->pluck('total', 'class_id')->toArray();
        }

        foreach($practiceData as $item) {
            // Skip items without practice relationship
            if (!$item->practice) {
                continue;
            }

            $week = Week::where('id',  $item->practice->week_id)->first();
            $weekName = $week ? $week->name : 'Unknown Week';
            $bookTitle = data_get($item, 'book.title', 'Unknown Book');
            $practiceName = data_get($item, 'practice.name', 'Unknown Practice');
            $item->name = $bookTitle.' - '.$weekName.' - '. $practiceName;
            $item->point_text = "";
            $item->duration = "";
            if($item->from) {
                $timeFrom = date('d/m/Y', strtotime($item->from));
                $timeTo = date('d/m/Y', strtotime($item->to));
                $item->duration = 'Từ '.$timeFrom.' đến '.$timeTo;
            }
            if (empty($item->to)) {
                $item->duration = 'Vô thời hạn';
            }

            $cid = data_get($item, 'class.id');
            $pid = data_get($item, 'practice.id');
            $agg = $pointAgg[$cid . '_' . $pid] ?? ['submitted' => 0, 'avg_point' => 0];
            $total = (int) ($classTotals[$cid] ?? 0);

            $item->teacher_name    = data_get($item, 'class.user.name', '');
            $item->total_students  = $total;
            $item->submitted_count = $agg['submitted'];
            $item->progress_text   = $agg['submitted'] . '/' . $total;
            $item->submit_rate     = $total > 0 ? round($agg['submitted'] * 100 / $total, 1) : 0;
            $item->submit_rate_text = $item->submit_rate . '%';
            $item->avg_point       = $agg['avg_point'];

            $item->is_rank = $agg['submitted'] > 0;
        }
        return $practiceData;
    }

    public function rankAssignedTask($classId, $practiceId) {
        $userClasses = UserClass::with(['user'])->where('class_id', $classId)->get();

        $userIds = $userClasses->pluck('user_id')->filter()->values()->toArray();

        // Load best point per user in 1 query (highest star_count, then lowest time)
        $points = Point::where('practice_id', $practiceId)
            ->where('class_id', $classId)
            ->where('is_assign', 1)
            ->whereIn('user_id', $userIds)
            ->orderBy('star_count', 'DESC')
            ->orderBy('time', 'ASC')
            ->get()
            ->groupBy('user_id')
            ->map(fn($group) => $group->first());

        // Load all point details for those points in 1 query
        $pointIds = $points->pluck('id')->toArray();
        $allDetails = PointDetail::whereIn('point_id', $pointIds)->get()->groupBy('point_id');

        $datas = [];
        foreach($userClasses as $item) {
            if(!isset($item->user->name)) {
                continue;
            }

            $data = [
                'time_text' => '',
                'point_text' => '',
                'point' => 0,
                'time' => 0,
                'point_id' => 0,
                'user_id' => $item->user_id,
                'name' => $item->user->name,
            ];

            $point = $points->get($item->user_id);
            if($point) {
                $seconds = (int)$point->time;
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;
                $data['time_text'] = $hours > 0
                    ? sprintf("%02d:%02d:%02d", $hours, $minutes, $secs)
                    : sprintf("%02d:%02d", $minutes, $secs);

                $pointDetails = $allDetails->get($point->id, collect());
                $totalPoint = count($pointDetails) * 10;
                $totalCorrect = $pointDetails->where('star_count', 1)->count();

                $data['point_id'] = $point->id;
                $data['point'] = $totalCorrect * 10;
                $data['time'] = $point->time;
                $data['point_text'] = ($totalCorrect * 10) . '/' . $totalPoint;
                $data['correct_answers_text'] = $totalCorrect . '/' . count($pointDetails);
            }
            $datas[] = $data;
        }
        usort($datas, function($a, $b) {
            // Điểm cao hơn xếp trước
            if ($a['point'] != $b['point']) {
                return $b['point'] <=> $a['point'];
            }
        
            // Nếu cùng điểm, ai có thời gian làm ít hơn xếp trước
            // Trường hợp chưa làm bài time = 0 thì đẩy xuống cuối
            $timeA = (int) $a['time'];
            $timeB = (int) $b['time'];
        
            if ($timeA === 0 && $timeB > 0) {
                return 1;
            }
        
            if ($timeB === 0 && $timeA > 0) {
                return -1;
            }
        
            return $timeA <=> $timeB;
        });
        
        $rankedData = [];
        $rank = 1;
        
        foreach ($datas as $index => $item) {
            if (
                $index > 0 &&
                $item['point'] == $datas[$index - 1]['point'] &&
                (int) $item['time'] == (int) $datas[$index - 1]['time']
            ) {
                // Chỉ cùng hạng nếu cùng điểm và cùng thời gian
                $item['rank'] = $rankedData[$index - 1]['rank'];
            } else {
                // Nếu khác điểm hoặc khác thời gian thì nhận hạng theo vị trí thực tế
                $item['rank'] = $index + 1;
            }
        
            $rankedData[] = $item;
        }
        return $rankedData;
    }
}

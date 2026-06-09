<?php

namespace App\Services;

use App\Helpers\DynamoDbHelper;
use App\Helpers\Helper;
use App\Models\User;
use App\Models\UserType;
use App\Repositories\PointDetailRepository;
use App\Repositories\PointRepository;
use App\Repositories\StreakRepository;
use App\Repositories\UserRepository;
use App\Services\Traits\ImageManagerTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserService
{
    use ImageManagerTrait;

    private $userRepository;

    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function register($data)
    {
        $password = data_get($data, 'password', '123456');
        $data['password'] = bcrypt($password);
        $data['email'] = isset($data['email']) ? strtolower($data['email']) : '';

        $dataUser = [
            'user_id_app' => $data['user_id'],
            'name' => isset($data['name']) ? $data['name'] : '',
            'username' => data_get($data, 'username'),
            'email' => isset($data['email']) ? $data['email'] : '',
            'tel' => isset($data['phone']) ? $data['phone'] : '',
            'password' => $data['password'],
            'status' => 'on',
            'user_type_id' => 6,
        ];

        $result = $this->userRepository->create($dataUser);

        return [
            'success' => true,
            'data' => $result,
            'message' => 'Đăng ký thành công'
        ];
    }

    public function update($request)
    {
        $user = $this->userRepository->first(['id' => $request['user_id']]);

        $dataUpdate['name'] = data_get($request, 'name');
        $dataUpdate['email'] = strtolower(data_get($request, 'email'));
        $dataUpdate['username'] = data_get($request, 'username');
        $dataUpdate['tel'] = data_get($request, 'phone');

        $this->userRepository->updateByFilters(['id' => $user->id], $dataUpdate);

        return ['success' => true, 'message' => 'cập nhật thành công'];
    }

    public function detail($request)
    {
        $user = $this->userRepository->first(['id' => $request['user_id']], [
            'with' => ['userInfo']
        ]);

        $user->img = $user->img ? Helper::getCloudFront($user->img) : null;
        $user->img_db = $user->img;
        $followInfo = app(UserFollowService::class)->getUserFollowInfo($request);
        $user->following = $followInfo['following'];
        $user->followers = $followInfo['followers'];
        $user->is_following = $followInfo['is_following'];
        return $user;
    }

    public function detailDirector($request)
    {
        return $this->userRepository->first(['id' => $request['user_id']], [
            'with' => ['userInfo', 'directorApp', 'directorApps']
        ]);
    }

    public function detailTeacher($request)
    {
        return $this->userRepository->first(['id' => $request['user_id']], [
            'with' => ['userInfo', 'teacherApp.app', 'teacherCourses', 'teacherEvents']
        ]);
    }
    public function detailStudent($request)
    {
        return $this->userRepository->first(['id' => $request['user_id']], [
            'with' => ['userInfo', 'classes.app', 'studentApp']
        ]);
    }

    public function uploadAvatar($userId, $file)
    {
        $user = $this->userRepository->first(['id' => $userId]);

        $avatarPath = 'user/avatars/';
        if ($user->img) {
            $this->deleteFile($avatarPath, $user->img);
        }

        $result = $this->uploadImg($file, $avatarPath);

        if (empty($result['status'])) {
            return [
                'success' => false,
                'message' =>  'avatar không đúng định dạng ảnh'
            ];
        } else {
            $this->userRepository->updateByFilters(['id' => $user->id], ['img' => $result['img']]);

            return [
                'success' => true,
                'url' => config('common.aws.cloud_front_domain') . '/' . $avatarPath . $result['img'],
                'message' =>  'Thay đổi ảnh đại diện thành công'
            ];
        }
    }

    public function getListUser($data)
    {
        return $this->userRepository->searchByFilters($data);
    }

    public function getListDirector($data)
    {
        return $this->userRepository->getListDirector($data);
    }

    public function getListTeacher($data)
    {
        return $this->userRepository->getListTeacher($data);
    }

    public function getListStudent($data)
    {
        return $this->userRepository->getListStudent($data);
    }

    public function store($data)
    {
        $user = Auth::user();
        $data['email'] = isset($data['email']) ? strtolower($data['email']) : '';
        $data['username'] = isset($data['username']) ? strtolower($data['username']) : '';
        $info = [
            'app_id' => data_get($data, 'app_id'),
            'name' => data_get($data, 'name'),
            'birthday' => data_get($data, 'birthday'),
            'email' => data_get($data, 'email', 'email'),
            'username' => data_get($data, 'username'),
            'tel' => data_get($data, 'tel'),
            'address' => data_get($data, 'address'),
            'user_type_id' => data_get($data, 'user_type_id'),
            'status' => 'on',
            'create_id' => $user->id,
            'create_by' => $user->name,
        ];

        if ($data['password']) {
            $info['password'] = bcrypt($data['password']);
        }

        if (empty($data['id'])) {
            $userType = UserType::where('id', $info['user_type_id'])->first();

            if ($userType->type === UserType::TYPE_STUDENT_APP) {
                $conditions = [
                    'TableName' => 'cst.users',
                    'IndexName' => 'search-by-login_name',
                    'KeyConditionExpression' => 'login_name = :login_name',
                    'ExpressionAttributeValues' => [
                        ':login_name' => ['S' => $data['username']],
                    ],
                ];

                $dynamoRecord = DynamoDbHelper::getItem($conditions);

                if (!empty($dynamoRecord)) {
                    Helper::logInfo('DynamoDbHelper:getItem Fail');
                    return false;
                }

                $uuid = Str::uuid()->toString();
                $dynamoInfo = [
                    'user_id' => $uuid,
                    'who_are_you' => 'student',
                    'created_at' => round(microtime(true) * 1000),
                    'phone_number' => data_get($data, 'tel'),
                    'hash' => $uuid,
                    'password' => sha1($data['password'] . $uuid),
                    'user_status' => "CONFIRMED",
                    'full_name' => data_get($data, 'name'),
                    'login_name' => data_get($data, 'username'),
                    'sms_status' => "CONFIRMED",
                    'verify_status' => 'VERIFIED',
                    'search_key' => "student#VERIFIED",
                ];

                $result = DynamoDbHelper::putItem('cst.users', $dynamoInfo);

                if (!empty($result)) {
                    $info['user_id_app'] = $dynamoInfo['user_id'];
                } else {
                    Helper::logInfo('DynamoDbHelper:putItem Fail');
                    return false;
                }
            }

            $record = $this->userRepository->create($info);
        } else {
            $record = $this->userRepository->first([
                'id' => $data['id']
            ]);
            $record->fill($info);
            $record->save();
            DynamoDbHelper::updateUserInfo($record->user_id_app, $data);
        }

        return $record;
    }

    public function delete($id)
    {
        $this->userRepository->deleteByFilter([
            'id' => $id
        ]);
    }

    public function getListStudentResultPractice($studentIds, $practiceId)
    {
        return $this->userRepository->getListStudentResultPractice($studentIds, $practiceId);
    }

    public function getListStudentResultClass($studentIds, $classId, $type)
    {
        return $this->userRepository->getListStudentResultClass($studentIds, $classId, $type);
    }

    public function getListStudentResultSchool($studentIds, $type)
    {
        return $this->userRepository->getListStudentResultSchool($studentIds, $type);
    }

    public function storeStudent($data, $key)
    {
        $info = [
            'name' => $data[$key]['name'],
            'birthday' => $data[$key]['birthday'],
            'email' => $data[$key]['email'],
            'username' => $data[$key]['username'],
            'tel' => $data[$key]['tel'],
            'address' => $data[$key]['address'],
            'user_type_id' => $data[$key]['user_type_id'],
            'status' => 'on',
            'password' => bcrypt(config('common.user.password_default'))
        ];

        $conditions = [
            'TableName' => 'cst.users',
            'IndexName' => 'search-by-login_name',
            'KeyConditionExpression' => 'login_name = :login_name',
            'ExpressionAttributeValues' => [
                ':login_name' => ['S' => $info['username']],
            ],
        ];

        $dynamoRecord = DynamoDbHelper::getItem($conditions);

        if (!empty($dynamoRecord)) {
            $info['user_id_app'] = $dynamoRecord["user_id"]["S"];
            return $this->userRepository->create($info);
        }

        $uuid = Str::uuid()->toString();
        $dynamoInfo = [
            'user_id' => $uuid,
            'who_are_you' => 'student',
            'created_at' => round(microtime(true) * 1000),
            'phone_number' => (string)$info['tel'],
            'hash' => $uuid,
            'password' => sha1(config('common.user.password_default') . $uuid),
            'user_status' => "CONFIRMED",
            'full_name' => $info['name'],
            'login_name' => $info['username'],
            'sms_status' => "CONFIRMED",
            'verify_status' => 'VERIFIED',
            'search_key' => "student#VERIFIED",
        ];

        $result = DynamoDbHelper::putItem('cst.users', $dynamoInfo);

        if (!empty($result)) {
            $info['user_id_app'] = $dynamoInfo['user_id'];
        } else {
            return false;
        }

        return $this->userRepository->create($info);
    }

    public function updateStudent($student, $data, $key)
    {
        $dataUpdate = [
          'password'  => config('common.user.password_default')
        ];
        DynamoDbHelper::updateUserInfo($student->user_id_app, $dataUpdate);

        $info = [
            'name' => $data[$key]['name'],
            'birthday' => $data[$key]['birthday'],
            'email' => $data[$key]['email'],
            'username' => $data[$key]['username'],
            'tel' => $data[$key]['tel'],
            'address' => $data[$key]['address'],
            'user_type_id' => $data[$key]['user_type_id'],
            'status' => 'on',
            'password' => bcrypt(config('common.user.password_default'))
        ];

        $student->fill($info);
        $student->save();
    }

    public function findById($id)
    {
        return $this->userRepository->find($id);
    }

    public function resetPassword($id)
    {
        return $this->userRepository->updateByFilters([
            'id' => $id
        ], [
            'password' => bcrypt(config('common.user.password_default'))
        ]);
    }

    public function getAchievements($data)
    {
        $highestStreak = app(StreakRepository::class)->getHighestStreakByUserId($data);
        $currentStreak = app(StreakRepository::class)->getCurrentStreakByUserId($data);
        $intelligencePoint = app(PointService::class)->getSummaryPoint($data);
        $eventHighestRank = app(EventService::class)->getHighestRank($data);

        $points = app(PointRepository::class)->findByUser($data['user_id'], ['pointDetails']);

        $perfectPracticePoint = 0;
        foreach ($points as $point) {
            $pointDetails = $point->pointDetails;
            if ($pointDetails->isNotEmpty()) {
                if ($pointDetails->sum('star_count') == count($pointDetails)) {
                    $perfectPracticePoint++;
                }
            }
        }

        return [
            'current_streak' => data_get($currentStreak, 'day_count', 0),
            'highest_streak' => data_get($highestStreak, 'day_count', 0),
            'intelligence_point' => $intelligencePoint['total_star_count'],
            'perfect_practice_point' => $perfectPracticePoint,
            'event_highest_rank' => data_get($eventHighestRank, 'highest_rank', 0),
        ];
    }
}

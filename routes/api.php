<?php

use App\Http\Controllers\Admin\ActiveCodeController as AdminActiveCodeController;
use App\Http\Controllers\Api\AppEditorController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\DeepviewEditorController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ArenaController;
use App\Http\Controllers\Api\NovelController;
use App\Http\Controllers\Api\PointController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\StarController;
use App\Http\Controllers\Api\TargetController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserFollowController;
use App\Http\Controllers\Api\UserProcessController;
use App\Http\Controllers\Api\UserSettingController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\UserTaskController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Api\ActiveCodeController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\PracticeController;
use App\Http\Controllers\Api\RealtimeEventController;
use App\Http\Controllers\Api\StreakController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['checkExitUser'])->group(function () {
    Route::prefix('target')->group(function () {
        Route::get('/daily', [TargetController::class, 'daily']);
        Route::post('/daily', [TargetController::class, 'postDaily']);
        Route::get('/sight', [TargetController::class, 'sight']);
        Route::post('/sight', [TargetController::class, 'postSight']);
    });

    Route::prefix('point')->group(function () {
        Route::post('practice', [PointController::class, 'postPractice']);
        Route::get('practice', [PointController::class, 'getPractice']);
        Route::post('theoretical', [PointController::class, 'postTheoretical']);
        Route::get('theoretical', [PointController::class, 'getTheoretical']);
        Route::get('total-practice', [PointController::class, 'getTotalPracticePoint']);
        Route::get('total-theoretical', [PointController::class, 'getTotalTheoreticalPoint']);
        Route::get('list-rank', [PointController::class, 'getListRank'])->withoutMiddleware(['checkExitUser']);
    });

    Route::prefix('user-task')->group(function () {
        Route::get('/', [UserTaskController::class, 'getUserTask']);
        Route::post('/', [UserTaskController::class, 'postUserTask']);
    });

    Route::prefix('order')->group(function () {
        Route::get('/', [OrderController::class, 'getOrder']);
        Route::post('/', [OrderController::class, 'postOrder']);

    });

    Route::prefix('comment')->group(function () {
        Route::post('/', [CommentController::class, 'saveComment']);
        Route::get('/', [CommentController::class, 'getList']);
    });

    Route::get('get-total-completed', [UserTaskController::class, 'getTotalCompleted']);
    Route::get('total-point-day', [PointController::class, 'getTotal']);
    Route::post('sub-total-point-day', [PointController::class, 'subTotal']);
    Route::get('total-practice-point', [PointController::class, 'getPracticePointDay']);

    Route::prefix('user-setting')->group(function () {
        Route::post('/', [UserSettingController::class, 'postSetting']);
        Route::get('/', [UserSettingController::class, 'getSetting']);
    });

    Route::post('login-social', [SocialController::class, 'login']);
    Route::get('search-user', [UserFollowController::class, 'searchUser']);

    Route::prefix('star')->group(function () {
        Route::post('/', [StarController::class, 'postStar']);
        Route::get('/', [StarController::class, 'getStar']);
    });

    Route::prefix('user-follow')->group(function () {
        Route::post('/', [UserFollowController::class, 'postUserFollow']);
        Route::get('/', [UserFollowController::class, 'getUserFollow']);
        Route::get('/info', [UserFollowController::class, 'getUserFollowInfo']);
    });

    Route::prefix('user-process')->group(function () {
        Route::post('/', [UserProcessController::class, 'postUserProcess']);
        Route::get('/', [UserProcessController::class, 'getUserProcess']);
    });

    Route::prefix('user')->group(function () {
        Route::post('/update', [UserController::class, 'update']);
        Route::get('/detail', [UserController::class, 'getDetail']);
        Route::post('/upload-avatar', [UserController::class, 'uploadAvatar']);
        Route::get('/badge', [BadgeController::class, 'getUserBadge']);
        Route::get('/inventory', [ItemController::class, 'inventory']);
        Route::get('/transaction', [TransactionController::class, 'getTransaction']);

        Route::get('/share', [ShareController::class, 'getUserShare']);
        Route::get('/user-practice', [AdminStudentController::class, 'getUserPractice']);

        Route::get('/room', [UserController::class, 'getRoom']);
        Route::post('/room', [UserController::class, 'postRoom']);
        Route::get('/achievements', [UserController::class, 'getAchievements']);
    });

    Route::prefix('badge')->group(function () {
        Route::get('/', [BadgeController::class, 'getBadge'])->withoutMiddleware(['checkExitUser']);
        Route::post('/', [BadgeController::class, 'postBadge']);
        Route::get('/detail', [BadgeController::class, 'getBadgeDetail'])->withoutMiddleware(['checkExitUser']);
    });

    Route::prefix('shop')->group(function () {
        Route::get('/items', [ItemController::class, 'getAllItem'])->withoutMiddleware(['checkExitUser']);
        Route::get('/item/detail', [ItemController::class, 'getDetail'])->withoutMiddleware(['checkExitUser']);
        Route::post('/purchase', [ItemController::class, 'purchase']);
        Route::get('/promotions', [ItemController::class, 'getAllPromotion'])->withoutMiddleware(['checkExitUser']);
    });

    Route::prefix('share')->group(function () {
        Route::get('/', [ShareController::class, 'getShare'])->withoutMiddleware(['checkExitUser']);
        Route::post('/', [ShareController::class, 'postShare']);
        Route::post('/click', [ShareController::class, 'postClickCount']);
    });

    Route::prefix('points')->name('points.')->group(function () {
        Route::get('result-by-class', [ResultController::class, 'getResultByClass'])->name('getResultByClass');
    });
});
Route::post('/active-code/send-mail', [AdminActiveCodeController::class, 'sendMail']);
Route::get('/check-code', [OrderController::class, 'checkActiveCode']);
Route::get('arena/getNowTimestamp', [ArenaController::class, 'getNowTimestamp'])->name('getNowTimestamp');
Route::post('register', [UserController::class, 'register']);
Route::get('get_book_all', [BookController::class, 'getBookAll']);
Route::get('get_app_editor_by_book', [AppEditorController::class, 'getAppEditorByBook']);
Route::get('get_deepview', [DeepviewEditorController::class, 'getDeepview']);
Route::get('novel/deepview', [NovelController::class, 'getDeepview']);
Route::post('/upload-file', [FileController::class, 'uploadFile'])->name('upload-file');
Route::post('/upload-file-presigned', [FileController::class, 'uploadFilePresigned'])->name('upload-file-presigned');

Route::middleware(['affAuthApi'])->group(function () {
    Route::prefix('student')->group(function () {
        Route::post('/create', [StudentController::class, 'create']);
        Route::post('/create-with-code', [StudentController::class, 'createWithCode']);
        Route::get('/assignment', [StudentController::class, 'getAssignment']);
        Route::get('/assignment-detail', [StudentController::class, 'getAssignmentDetail']);
    });

    Route::prefix('active-code')->group(function () {
        Route::post('/create', [ActiveCodeController::class, 'create']);
    });

    Route::get('/events', [EventController::class, 'getList']);
    Route::post('/event/update-point', [EventController::class, 'updatePoint']);
    Route::get('/event/ranking', [EventController::class, 'getRanking']);
    Route::get('/event/user-detail', [EventController::class, 'getUserDetail']);
    Route::get('/event/user-ranking', [EventController::class, 'getUserRanking']);
    Route::get('/event/{id}', [EventController::class, 'detail']);
    Route::get('/event/user/highest-rank', [EventController::class, 'getHighestRank']);

    Route::prefix('practice')->group(function () {
        Route::get('/{id}/reading/list-images', [PracticeController::class, 'getListImages'])->name('reading.list-images');
    });

    Route::prefix('realtime-events')->group(function () {
        Route::get('/', [RealtimeEventController::class, 'getList']);
        Route::post('/create', [RealtimeEventController::class, 'create']);
        Route::post('/kick-user', [RealtimeEventController::class, 'kickUser']);
        Route::post('/invite', [RealtimeEventController::class, 'invite']);
        Route::post('/accept-invite', [RealtimeEventController::class, 'acceptInvite']);
        Route::post('/update-point', [RealtimeEventController::class, 'updatePoint']);
        Route::get('/ranking', [RealtimeEventController::class, 'getRanking']);
        Route::get('/user-ranking', [RealtimeEventController::class, 'getUserRanking']);
        Route::get('/{id}', [RealtimeEventController::class, 'detail']);
    });

    Route::prefix('streaks')->group(function () {
        Route::get('/get-by-user', [StreakController::class, 'getByUser']);
        Route::post('/update', [StreakController::class, 'update']);
    });
});

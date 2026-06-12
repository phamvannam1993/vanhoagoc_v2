<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VoiceTypeController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\LessonQuizGeneratorController;
use App\Http\Controllers\LessonGeneratorController;
use App\Http\Controllers\LessonDraftController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionEditorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SelectSampleController;
use App\Http\Controllers\WeekController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Hr\AuthHrController;
use App\Http\Controllers\Hr\PointController;
use App\Http\Controllers\VoiceController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DirectorController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\PracticeController;
use App\Http\Controllers\Admin\ActiveCodeController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\CompetencyController;
use App\Http\Controllers\Admin\CompetencyComponentController;
use App\Http\Controllers\Admin\EducationalContentController;
use App\Http\Controllers\Admin\TeachingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;


Route::middleware(['auth', 'checkUserRole'])->group(function () {
    Route::middleware(['checkUserSystemRole'])->group(function () {
        Route::get('/', [AppController::class, 'index'])->name('dashboard');
        // apps
        Route::prefix('apps')->name('apps.')->group(function () {
            Route::get('/', [AppController::class, 'index'])->name('dashboard');
            Route::get('/create', [AppController::class, 'create'])->name('create');
            Route::get('/edit/{id}', [AppController::class, 'edit'])->name('edit');
            Route::get('/detail/{id}', [AppController::class, 'detail'])->name('detail');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [AppController::class, 'jsonList'])->name('list');
                Route::post('/store', [AppController::class, 'store'])->name('store');
                Route::post('/update', [AppController::class, 'update'])->name('update');
                Route::post('/visible', [AppController::class, 'visible'])->name('visible');
                Route::delete('/{id}', [AppController::class, 'delete'])->name('delete');
                Route::patch('/update-position', [AppController::class, 'updatePosition'])->name('updatePosition');

                Route::post('/clone', [AppController::class, 'clone'])->name('clone');
            });
        });

        // books
        Route::prefix('books')->name('books.')->group(function () {
            Route::get('/', [BookController::class, 'index'])->name('index');
            Route::get('/create', [BookController::class, 'create'])->name('create');
            Route::get('/{id}', [BookController::class, 'edit'])->name('edit');
            Route::get('/detail/{id}', [BookController::class, 'detail'])->name('detail');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [BookController::class, 'jsonList'])->name('list');
                Route::post('/store', [BookController::class, 'store'])->name('store');
                Route::post('/update', [BookController::class, 'update'])->name('update');
                Route::post('/visible', [BookController::class, 'visible'])->name('visible');
                Route::delete('/{id}', [BookController::class, 'delete'])->name('delete');
                Route::patch('/update-position', [BookController::class, 'updatePosition'])->name('updatePosition');
                Route::post('/copy-data', [BookController::class, 'copyData'])->name('copyData');
            });
        });

        Route::get('storage/private/editor/img/week/{filename}', function ($filename) {
            $path = storage_path('app/private/editor/img/week/' . $filename);

            if (!file_exists($path)) {
                abort(404);
            }

            return Response::make(file_get_contents($path), 200, [
                'Content-Type' => mime_content_type($path),
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            ]);
        });
        // weeks
        Route::prefix('weeks')->name('weeks.')->group(function () {
            Route::get('/', [WeekController::class, 'index'])->name('index');
            Route::get('/create', [WeekController::class, 'create'])->name('create');
            Route::get('/edit/{id}', [WeekController::class, 'edit'])->name('edit');
            Route::get('/detail/{id}', [WeekController::class, 'detail'])->name('detail');


            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [WeekController::class, 'jsonList'])->name('list');
                Route::post('/store', [WeekController::class, 'store'])->name('store');
                Route::post('/copy-data', [WeekController::class, 'copyData'])->name('copyData');
                Route::post('/delete-many', [WeekController::class, 'deleteMany'])->name('deleteMany');
                Route::post('/update', [WeekController::class, 'update'])->name('update');
                Route::post('/visible', [WeekController::class, 'visible'])->name('visible');
                Route::delete('/{id}', [WeekController::class, 'delete'])->name('delete');
                Route::patch('/update-position', [WeekController::class, 'updatePosition'])->name('updatePosition');
            });
        });

        // profile
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'edit'])->name('edit');
            Route::patch('/', [ProfileController::class, 'update'])->name('update');
            Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');

            Route::post('/updatePassword', [ProfileController::class, 'updatePassword'])->name('updatePassword');
        });

        Route::prefix('voice-types')->name('voice-types.')->group(function () {
            Route::get('/list', [VoiceTypeController::class, 'list'])->name('list');
            Route::post('/text-to-speech', [VoiceTypeController::class, 'textToSpeech'])->name('textToSpeech');
            Route::post('/vbee-callback', [VoiceTypeController::class, 'handleCallback'])->name('vbee.callback');
        });

        // Lesson
        Route::prefix('lessons')->name('lessons.')->group(function () {
            Route::get('/', [LessonController::class, 'index'])->name('index');
            Route::get('/create', [LessonController::class, 'create'])->name('create');
            Route::get('/ai-create', [LessonController::class, 'aiCreate'])->name('aiCreate');
            Route::get('/edit', [LessonController::class, 'edit'])->name('edit');
            Route::get('/detail/{id}', [LessonController::class, 'detail'])->name('detail');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [LessonController::class, 'jsonList'])->name('list');
                Route::post('/store', [LessonController::class, 'store'])->name('store');
                Route::post('/update', [LessonController::class, 'update'])->name('update');
                Route::post('/visible', [LessonController::class, 'visible'])->name('visible');
                Route::delete('/{id}', [LessonController::class, 'delete'])->name('delete');
                Route::post('/copy-data', [LessonController::class, 'copyData'])->name('copyData');
                Route::post('/delete-multiple', [LessonController::class, 'deleteMultiple'])->name('deleteMultiple');
                Route::post('/reset-position', [LessonController::class, 'resetPosition'])->name('reset-position');
                Route::patch('/update-position', [LessonController::class, 'updatePosition'])->name('updatePosition');

                // Lesson generation API
                Route::post('/generate-text', [LessonGeneratorController::class, 'generateText'])->name('generateText');
                Route::post('/generate-audio', [LessonGeneratorController::class, 'generateAudio'])->name('generateAudio');
                Route::post('/generate-bundle', [LessonGeneratorController::class, 'generateBundle'])->name('generateBundle');
                Route::get('/task-status', [LessonGeneratorController::class, 'taskStatus'])->name('taskStatus');
                Route::get('/bundle-status', [LessonGeneratorController::class, 'getBundleStatus'])->name('getBundleStatus');
                Route::post('/save-audio-result', [LessonGeneratorController::class, 'saveAudioResult'])->name('saveAudioResult');
                Route::post('/save-bundle-result', [LessonGeneratorController::class, 'saveBundleResult'])->name('saveBundleResult');
                Route::post('/save-assign-exercises', [LessonGeneratorController::class, 'saveAssignExercises'])->name('saveAssignExercises');
                Route::post('/save-lesson-practice-questions', [LessonGeneratorController::class, 'saveLessonPracticeQuestions'])->name('saveLessonPracticeQuestions');
                Route::post('/create-student-exercise', [LessonGeneratorController::class, 'createStudentExercise'])->name('createStudentExercise');
                Route::get('/students-by-class', [LessonGeneratorController::class, 'getStudentsByClass'])->name('studentsByClass');
                Route::post('/assign-exercises', [LessonGeneratorController::class, 'assignExercises'])->name('assignExercises');
                Route::post('/upload-multi-file', [LessonQuizGeneratorController::class, 'uploadMultiFile'])->name('uploadMultiFile');

                // Lesson draft history
                Route::get('/draft/history', [LessonDraftController::class, 'history'])->name('draftHistory');
                Route::get('/draft/preview', [LessonDraftController::class, 'preview'])->name('draftPreview');
                Route::post('/draft/approve', [LessonDraftController::class, 'approve'])->name('draftApprove');
                Route::post('/draft/save-text', [LessonDraftController::class, 'saveTextContent'])->name('saveTextContent');
                Route::delete('/draft/delete', [LessonDraftController::class, 'delete'])->name('draftDelete');
            });

            // reading
            Route::prefix('reading')->name('reading.')->group(function () {
                Route::get('/create', [ReadingController::class, 'create'])->name('create');
                Route::get('/edit', [ReadingController::class, 'edit'])->name('edit');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::post('/store', [ReadingController::class, 'store'])->name('store');
                    Route::post('/update', [ReadingController::class, 'update'])->name('update');
                    Route::post('/update-image', [ReadingController::class, 'updateImage'])->name('update-image');
                });
            });

            // video
            Route::prefix('video')->name('video.')->group(function () {
                Route::get('/create', [VideoController::class, 'create'])->name('create');
                Route::get('/edit', [VideoController::class, 'edit'])->name('edit');
                Route::get('/history', [VideoController::class, 'history'])->name('history');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::post('/store', [VideoController::class, 'store'])->name('store');
                });
            });

            // voice
            Route::prefix('voice')->name('voice.')->group(function () {
                Route::get('/create', [VoiceController::class, 'create'])->name('create');
                Route::get('/edit', [VoiceController::class, 'edit'])->name('edit');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::post('/store', [VoiceController::class, 'store'])->name('store');
                });
            });

            // generate quiz
            Route::get('/generate-quiz', [LessonQuizGeneratorController::class, 'show'])->name('generateQuiz');
            Route::post('/json/generate-quiz', [LessonQuizGeneratorController::class, 'generate'])->name('json.generateQuiz');
            Route::post('/json/save-quiz', [LessonQuizGeneratorController::class, 'saveQuiz'])->name('json.saveQuiz');
            Route::post('/json/upload-multi-file', [LessonQuizGeneratorController::class, 'uploadMultiFile'])->name('json.uploadMultiFile');
        });

        // question
        Route::prefix('questions')->name('questions.')->group(function () {
            Route::get('/', [QuestionController::class, 'index'])->name('index');
            Route::get('/create', [QuestionController::class, 'create'])->name('create');
            Route::get('/upload', [\App\Http\Controllers\QuestionUploadController::class, 'show'])->name('upload');
            Route::post('/json/save-question', [\App\Http\Controllers\QuestionUploadController::class, 'saveQuestion'])->name('json.saveQuestion');
            Route::get('/edit', [QuestionController::class, 'edit'])->name('edit');
            Route::get('/answer', [QuestionController::class, 'answer'])->name('answer');
            Route::get('/answer/edit', [QuestionController::class, 'editAnswer'])->name('editAnswer');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [QuestionController::class, 'jsonList'])->name('list');
                Route::post('/store', [QuestionController::class, 'store'])->name('store');
                Route::post('/update', [QuestionController::class, 'store'])->name('update');
                Route::delete('/{id}', [QuestionController::class, 'delete'])->name('delete');
                Route::post('/storeAnswer', [QuestionController::class, 'storeAnswer'])->name('storeAnswer');
                Route::get('/answer', [QuestionController::class, 'jsonAnswer'])->name('answer');
                Route::patch('/update-position', [QuestionController::class, 'updatePosition'])->name('updatePosition');
                Route::post('/import', [QuestionController::class, 'importQuestions'])->name('import');
            });
        });

        // question
        Route::prefix('comments')->name('comments.')->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('index');
            Route::get('/app/{id}', [CommentController::class, 'listCommentApp'])->name('listCommentApp');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [CommentController::class, 'jsonList'])->name('list');
                Route::delete('/{id}', [CommentController::class, 'delete'])->name('delete');
                Route::post('/{id}', [CommentController::class, 'update'])->name('update');
            });
        });

        // exercise
        Route::prefix('questionEditors')->name('questionEditors.')->group(function () {
            Route::get('/', [QuestionEditorController::class, 'index'])->name('index');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [QuestionEditorController::class, 'jsonList'])->name('list');
                Route::post('/store', [QuestionController::class, 'store'])->name('store');
                Route::put('/update', [QuestionController::class, 'update'])->name('update');
                Route::post('/store-exercise', [QuestionEditorController::class, 'storeExercise'])->name('storeExercise');
                Route::post('/store-exercise-question', [QuestionEditorController::class, 'storeExerciseQuestion'])->name('storeExerciseQuestion');
                Route::post('/games/store', [QuestionEditorController::class, 'storeGame'])->name('storeGame');

                Route::delete('/{id}', [QuestionEditorController::class, 'delete'])->name('delete');
                Route::post('/delete-multiple', [QuestionEditorController::class, 'deleteMultiple'])->name('deleteMultiple');

                Route::post('/copy-data', [QuestionEditorController::class, 'copyData'])->name('copyData');
                Route::post('/duplicate', [QuestionEditorController::class, 'duplicate'])->name('duplicate');
                Route::post('/visible', [QuestionEditorController::class, 'visible'])->name('visible');
                Route::post('/reset-position', [QuestionEditorController::class, 'resetPosition'])->name('reset-position');
                Route::patch('/update-position', [QuestionEditorController::class, 'updatePosition'])->name('updatePosition');
            });

            Route::get('/create', [QuestionEditorController::class, 'createExercise'])->name('createExercise');
            Route::get('/import-questions', [QuestionEditorController::class, 'importExercise'])->name('importExercise');

            Route::get('/create-game', [SelectSampleController::class, 'getCreateGame'])->name('createGame');
            Route::get('/edit-game', [SelectSampleController::class, 'getEditGame'])->name('editGame');
            Route::prefix('json')->name('json.')->group(function () {
                Route::post('/create-game', [SelectSampleController::class, 'postCreateGame'])->name('postCreateGame');
                Route::post('/edit-game', [SelectSampleController::class, 'postEditGame'])->name('postEditGame');
            });
        });

        // Assigned Exercises (Bài giao học sinh)
        Route::prefix('assigned-exercises')->name('assignedExercises.')->group(function () {
            Route::get('/', [LessonGeneratorController::class, 'assignedExercisesList'])->name('index');
            Route::get('/{exercise_id}/items', [LessonGeneratorController::class, 'exerciseItemsList'])->name('items');
            Route::get('/{exercise_id}/items/{item_id}/questions', [LessonGeneratorController::class, 'exerciseQuestionsList'])->name('questions');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [LessonGeneratorController::class, 'jsonAssignedExercisesList'])->name('list');
                Route::get('/{exercise_id}/items', [LessonGeneratorController::class, 'jsonExerciseItemsList'])->name('itemsList');
                Route::get('/{item_id}/questions', [LessonGeneratorController::class, 'jsonExerciseQuestionsList'])->name('questionsList');
            });
        });

        // Template
        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [TemplateController::class, 'index'])->name('index');
            Route::get('/create', [TemplateController::class, 'create'])->name('create');
            Route::get('/edit', [TemplateController::class, 'edit'])->name('edit');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/get-template', [TemplateController::class, 'listTemplate'])->name('listTemplate');
                Route::get('/get-template-app', [TemplateController::class, 'listTemplateApp'])->name('listTemplateApp');
                Route::get('/detail', [TemplateController::class, 'detail'])->name('detail');
                Route::post('/copy-data', [TemplateController::class, 'copyData'])->name('copyData');
                Route::post('/delete-many', [TemplateController::class, 'deleteMany'])->name('deleteMany');
                Route::get('/list', [TemplateController::class, 'list'])->name('list');
                Route::post('/store', [TemplateController::class, 'store'])->name('store');
                Route::delete('/{id}', [TemplateController::class, 'delete'])->name('delete');
                Route::post('/visible', [TemplateController::class, 'visible'])->name('visible');
                Route::patch('/update-position', [TemplateController::class, 'updatePosition'])->name('updatePosition');
            });
        });

        // Account management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AccountController::class, 'index'])->name('index');
            Route::get('/member', [AccountController::class, 'member'])->name('member');
            Route::get('/new', [AccountController::class, 'newMember'])->name('newMember');
            Route::get('/edit-member', [AccountController::class, 'editMember'])->name('editMember');
            Route::get('/update-account', [AccountController::class, 'updateAccount'])->name('updateAccount');
            Route::get('/assign-role', [AccountController::class, 'assignRole'])->name('assignRole');
            Route::get('/list', [AccountController::class, 'list'])->name('list');
            Route::get('/edit-access-module', [AccountController::class, 'editAccessModule'])->name('editAccessModule');
            Route::post('/update-access-module', [AccountController::class, 'updateAccessModule'])->name('updateAccessModule');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [AccountController::class, 'jsonList'])->name('list');
                Route::get('/list-member', [AccountController::class, 'jsonListMember'])->name('jsonListMember');
                Route::get('/list-app', [AccountController::class, 'jsonListApp'])->name('jsonListApp');
                Route::get('/list-user', [AccountController::class, 'jsonListUser'])->name('jsonListUser');
                Route::get('/edit-access-module', [AccountController::class, 'jsonEditAccessModule'])->name('jsonEditAccessModule');
                Route::post('/create-member', [AccountController::class, 'jsonCreateMember'])->name('createMember');
                Route::post('/edit-member', [AccountController::class, 'jsonEditMember'])->name('editMember');
                Route::post('/update-member', [AccountController::class, 'jsonUpdateMember'])->name('updateMember');
                Route::post('/assign-role', [AccountController::class, 'jsonAssignRole'])->name('assignRole');
                Route::delete('/{id}', [AccountController::class, 'deleteMember'])->name('deleteMember');
            });
        });
    });

    // Admin management
    Route::prefix('admins')->name('admins.')->group(function () {
        Route::middleware(['checkAdminRole'])->group(function () {
            Route::prefix('directors')->name('directors.')->group(function () {
                Route::get('/', [DirectorController::class, 'index'])->name('index');
                Route::get('/create', [DirectorController::class, 'create'])->name('create');
                Route::get('/edit', [DirectorController::class, 'edit'])->name('edit');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::get('/list', [DirectorController::class, 'jsonList'])->name('jsonList');
                    Route::post('/store', [DirectorController::class, 'store'])->name('store');
                    Route::delete('/delete-multiple', [DirectorController::class, 'deleteMultiple'])->name('deleteMultiple');
                    Route::delete('/{id}', [DirectorController::class, 'delete'])->name('delete');
                    Route::post('/{id}', [DirectorController::class, 'resetPassword'])->name('resetPassword');
                });
            });
        });

        Route::middleware(['checkSchoolRole'])->group(function () {
            Route::prefix('school')->name('school.')->group(function () {
                Route::get('/', [SchoolController::class, 'index'])->name('index');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::get('/list', [SchoolController::class, 'jsonList'])->name('list');
                });
            });

            Route::prefix('teachers')->name('teachers.')->group(function () {
                Route::get('/', [TeacherController::class, 'index'])->name('index');
                Route::get('/create', [TeacherController::class, 'create'])->name('create');
                Route::get('/edit', [TeacherController::class, 'edit'])->name('edit');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::get('/list', [TeacherController::class, 'jsonList'])->name('jsonList');
                    Route::get('/get-class-by-app', [TeacherController::class, 'getClassByApp'])->name('getClassByApp');
                    Route::get('/get-course-by-app', [TeacherController::class, 'getCourseByApp'])->name('getCourseByApp');
                    Route::get('/get-event-by-app', [TeacherController::class, 'getEventByApp'])->name('getEventByApp');
                    Route::post('/store', [TeacherController::class, 'store'])->name('store');
                    Route::delete('/delete-multiple', [TeacherController::class, 'deleteMultiple'])->name('deleteMultiple');
                    Route::delete('/{id}', [TeacherController::class, 'delete'])->name('delete');
                    Route::post('/{id}', [TeacherController::class, 'resetPassword'])->name('resetPassword');
                });
            });
        });

        Route::middleware(['checkClassRole'])->group(function () {
            // Trang trung gian: chọn Lớp/Đơn vị trước khi Giao bài / Xem kết quả
            Route::get('giao-bai', [TeachingController::class, 'assign'])->name('teaching.assign');
            Route::get('ket-qua', [TeachingController::class, 'result'])->name('teaching.result');

            Route::prefix('class')->name('class.')->group(function () {
                Route::get('/', [ClassController::class, 'index'])->name('index');
                Route::get('/create', [ClassController::class, 'create'])->name('create');
                Route::get('/edit', [ClassController::class, 'edit'])->name('edit');
                Route::get('/result', [ClassController::class, 'result'])->name('result');
                Route::get('/rank', [ClassController::class, 'rank'])->name('rank');
                Route::get('/result-learn', [ClassController::class, 'resultLearn'])->name('resultLearn');
                Route::get('/export-result', [ClassController::class, 'exportResult'])->name('exportResult');
                Route::get('/assignment', [ClassController::class, 'assignment'])->name('assignment');
                Route::get('/rank-list', [ClassController::class, 'showRank'])->name('showRank');
                Route::get('/export-rank', [ClassController::class, 'exportRank'])->name('exportRank');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::get('/list', [ClassController::class, 'jsonList'])->name('list');
                    Route::post('/store', [ClassController::class, 'store'])->name('store');
                    Route::delete('/{id}', [ClassController::class, 'delete'])->name('delete');
                    Route::post('/delete-assign', [ClassController::class, 'deleteAssign'])->name('deleteAssign');
                    Route::get('/assigned-task', [ClassController::class, 'assignedTask'])->name('assignedTask');
                    Route::get('/rank-assigned-task', [ClassController::class, 'rankAssignedTask'])->name('rankAssignedTask');
                    Route::get('/free-practic', [ClassController::class, 'freePractic'])->name('freePractic');
                });
            });


            Route::prefix('active-codes')->name('active-codes.')->group(function () {
                Route::get('/', [ActiveCodeController::class, 'index'])->name('index');
                Route::post('/', [ActiveCodeController::class, 'save'])->name('save');
                Route::get('/create', [ActiveCodeController::class, 'create'])->name('create');
                Route::prefix('json')->name('json.')->group(function () {
                    Route::get('/list', [ActiveCodeController::class, 'jsonList'])->name('list');
                    Route::get('/export-data', [ActiveCodeController::class, 'exportData'])->name('exportData');
                    Route::delete('/delete-multiple', [ActiveCodeController::class, 'deleteMultiple'])->name('deleteMultiple');
                });
            });

            Route::prefix('students')->name('students.')->group(function () {
                Route::get('/', [StudentController::class, 'index'])->name('index');
                Route::get('/create', [StudentController::class, 'create'])->name('create');
                Route::get('/result', [StudentController::class, 'result'])->name('result');
                Route::get('/edit', [StudentController::class, 'edit'])->name('edit');
                Route::get('/point-detail', [StudentController::class, 'pointDetail'])->name('pointDetail');
                Route::post('/post-order', [StudentController::class, 'postOrderMulti'])->name('postOrderMulti');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::get('/list', [StudentController::class, 'jsonList'])->name('jsonList');
                    Route::get('/get-class-by-app', [StudentController::class, 'getClassByApp'])->name('getClassByApp');
                    Route::post('/assign-class', [StudentController::class, 'assignClass'])->name('assignClass');
                    Route::post('/store', [StudentController::class, 'store'])->name('store');
                    Route::post('/import-student', [StudentController::class, 'importStudent'])->name('importStudent');
                    Route::get('/export-students', [StudentController::class, 'exportStudent'])->name('exportStudent');
                    Route::delete('/delete-multiple', [StudentController::class, 'deleteMultiple'])->name('deleteMultiple');
                    Route::delete('/{id}', [StudentController::class, 'delete'])->name('delete');
                    Route::delete('/order/{id}', [StudentController::class, 'deleteOrder'])->name('deleteOrder');
                    Route::post('/{id}', [StudentController::class, 'resetPassword'])->name('resetPassword');
                    Route::get('/assigned-task', [StudentController::class, 'assignedTask'])->name('assignedTask');
                    Route::get('/free-practice', [StudentController::class, 'freePractice'])->name('freePractice');
                    Route::get('/export-student-result', [StudentController::class, 'exportStudentResult'])->name('exportStudentResult');
                    Route::get('/export-point-detail', [StudentController::class, 'exportPointDetail'])->name('exportPointDetail');

                });
            });
            Route::prefix('practices')->name('practices.')->group(function () {
                Route::get('/', [PracticeController::class, 'index'])->name('index');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::get('/getLesson', [PracticeController::class, 'getLesson'])->name('getLesson');
                    Route::post('/assign-practice', [PracticeController::class, 'assignPractice'])->name('assignPractice');
                    Route::post('/withdraw-practice', [PracticeController::class, 'withDrawPractice'])->name('withDrawPractice');
                });
            });
            Route::prefix('points')->name('points.')->group(function () {
                Route::get('result-practice', [ResultController::class, 'getResultPractice'])->name('getResultPractice');
                Route::get('result-class', [ResultController::class, 'getResultClass'])->name('getResultClass');
                Route::get('result-school', [ResultController::class, 'getResultSchool'])->name('getResultSchool');

                Route::prefix('json')->name('json.')->group(function () {
                    Route::get('result', [ResultController::class, 'getResult'])->name('getResult');
                    Route::get('result-by-practice', [ResultController::class, 'getResultByPractice'])->name('getResultByPractice');
                    Route::get('result-by-class', [ResultController::class, 'getResultByClass'])->name('getResultByClass');
                    Route::get('result-by-school', [ResultController::class, 'getResultBySchool'])->name('getResultBySchool');
                    Route::get('total-practice', [ResultController::class, 'getTotalPracticePoint'])->name('getTotalPracticePoint');
                    Route::get('total-theoretical', [ResultController::class, 'getTotalTheoreticalPoint'])->name('getTotalTheoreticalPoint');
                });

            });
        });
        Route::prefix('competencies')->name('competencies.')->group(function () {
            Route::get('/', [CompetencyController::class, 'index'])->name('index');
            Route::get('/create', [CompetencyController::class, 'create'])->name('create');
            Route::get('/edit', [CompetencyController::class, 'edit'])->name('edit');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [CompetencyController::class, 'jsonList'])->name('list');
                Route::post('/store', [CompetencyController::class, 'store'])->name('store');
                Route::delete('/{id}', [CompetencyController::class, 'delete'])->name('delete');
            });
        });

        Route::prefix('competency-components')->name('competency-components.')->group(function () {
            Route::get('/', [CompetencyComponentController::class, 'index'])->name('index');
            Route::get('/create', [CompetencyComponentController::class, 'create'])->name('create');
            Route::get('/edit', [CompetencyComponentController::class, 'edit'])->name('edit');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [CompetencyComponentController::class, 'jsonList'])->name('list');
                Route::post('/store', [CompetencyComponentController::class, 'store'])->name('store');
                Route::delete('/{id}', [CompetencyComponentController::class, 'delete'])->name('delete');
            });
        });

        Route::prefix('educational-contents')->name('educational-contents.')->group(function () {
            Route::get('/', [EducationalContentController::class, 'index'])->name('index');
            Route::get('/create', [EducationalContentController::class, 'create'])->name('create');
            Route::get('/edit', [EducationalContentController::class, 'edit'])->name('edit');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [EducationalContentController::class, 'jsonList'])->name('list');
                Route::post('/store', [EducationalContentController::class, 'store'])->name('store');
                Route::delete('/{id}', [EducationalContentController::class, 'delete'])->name('delete');
            });
        });

        Route::post('/event/json/update', [EventController::class, 'jsonUpdate'])->name('event.json.update');
        Route::patch('event/{id}/activate', [EventController::class, 'activate'])->name('event.activate');
        Route::get('event/{id}/ranking', [EventController::class, 'ranking'])->name('event.ranking');
        Route::get('event/{id}/export-ranking', [EventController::class, 'exportRanking'])->name('event.export-ranking');
        Route::get('/event/json/get-classes-by-app', [EventController::class, 'getClassesByApp'])->name('event.json.getClassesByApp');
        Route::get('/event/json/check-overlap', [EventController::class, 'checkOverlap'])->name('event.json.checkOverlap');

        Route::resource('event', EventController::class);
    });
});

Route::prefix('hr')->name('hr.')->group(function () {
    Route::get('/login', [AuthHrController::class, 'showFormLogin'])->middleware(['CheckGuest'])->name('showFormLogin');
    Route::post('/login', [AuthHrController::class, 'login'])->name('login');

    Route::middleware(['auth:hr'])->group(function () {
        Route::prefix('point')->name('point.')->group(function () {
            Route::get('/', [PointController::class, 'index'])->name('index');

            Route::prefix('json')->name('json.')->group(function () {
                Route::get('/list', [PointController::class, 'jsonList'])->name('list');
            });
        });
    });
});

Route::get('/403', function () {
    return inertia('Errors/Forbidden');
})->name('403');

require __DIR__ . '/auth.php';

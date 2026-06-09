<?php

namespace App\Http\Controllers;

use App\Repositories\PracticeRepository;
use App\Repositories\QuestionEditorRepository;
use App\Services\ExerciseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SelectSampleController extends Controller
{
    protected $questionEditorRepository;
    protected $practiceRepository;

    public function __construct(QuestionEditorRepository $questionEditorRepository, PracticeRepository $practiceRepository)
    {
        $this->questionEditorRepository = $questionEditorRepository;
        $this->practiceRepository = $practiceRepository;
    }

    public function getCreateGame(Request $request, ExerciseService $exerciseService)
    {
        \Symfony\Component\VarDumper\VarDumper::setHandler(function () {});

        $params = $request->all([
            'practice_id',
            'template_id'
        ]);

        $template = $exerciseService->getQuestionTemplate($params['template_id']);

        if (empty($template)) {
            abort(404, 'Không tìm thấy template: ' . ($params['template_id'] ?? 'N/A'));
        }

        $view = $exerciseService->getViewPathForCreate($template->type);

        if (empty($view)) {
            abort(404, 'Không tìm thấy view cho loại câu hỏi: ' . $template->type);
        }

        $practiceId = $request->get('practice_id');
        $practice = $this->practiceRepository->findOrFail($practiceId);
        $practice->load('week.book.app');

        return Inertia::render($view, [
            'query' => $request->query(),
            'template' => $template,
            'practice' => $practice
        ]);
    }

    public function postCreateGame(Request $request, ExerciseService $exerciseService)
    {
        $params = $request->all();

        $exerciseService->saveQuestionEditor($params);

        return response()->json([
            'status' => true,
        ]);
    }

    public function getEditGame(Request $request, ExerciseService $exerciseService)
    {
        $id = $request->get('id');

        $result = $exerciseService->getDetailById($id);

        $view = $exerciseService->getViewPathForEdit($result['template']->type);
        if(empty($view)) {
            $view = 'SelectSample/EditDragDrop';
        }
        $questionEditor = $this->questionEditorRepository->findOrFail($id);
        $questionEditor->load('practice.week.book.app');

        return Inertia::render($view, [
            'query' => $request->query(),
            'template' => $result['template'],
            'data' => $result['data'],
            'questionEditor' => $questionEditor
        ]);
    }

    public function postEditGame(Request $request, ExerciseService $exerciseService)
    {
        $params = $request->all();

        $exerciseService->saveQuestionEditor($params, $params['id']);

        return response()->json([
            'status' => true
        ]);
    }

    public function getCreateChooseCorrect(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id'
        ]);
        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('Question/New', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getEditChooseCorrect(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);
        //      todo check pass id, param type, sample_id để trả thêm dữ liệu của câu hỏi luyện tập
        return Inertia::render('SelectSample/EditChooseCorrect', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getDetailChooseCorrect(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);
        //      todo check pass id, param type, sample_id để trả thêm dữ liệu của câu hỏi luyện tập
        return Inertia::render('SelectSample/DetailChooseCorrect', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getCreateDragDrop(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id'
        ]);
        //      todo check pass param type, sample_id để trả thêm dữ liệu

        return Inertia::render('SelectSample/CreateDragDrop', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getEditDragDrop(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);
        //      todo check pass param type, sample_id để trả thêm dữ liệu

        return Inertia::render('SelectSample/EditDragDrop', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getDetailDragDrop(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);
        //      todo check pass param type, sample_id để trả thêm dữ liệu

        return Inertia::render('SelectSample/DetailDragDrop', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getCreateConnectSentence(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id'
        ]);

        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('SelectSample/CreateConnectSentence', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getEditConnectSentence(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);

        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('SelectSample/EditConnectSentence', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getDetailConnectSentence(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);

        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('SelectSample/DetailConnectSentence', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getCreateArrange(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id'
        ]);

        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('SelectSample/CreateArrange', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getEditArrange(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);

        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('SelectSample/EditArrange', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getDetailArrange(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);

        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('SelectSample/DetailArrange', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getCreateMatchPhoto(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id'
        ]);

        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('SelectSample/CreateMatchPhoto', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getEditMatchPhoto(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);

        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('SelectSample/EditMatchPhoto', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }

    public function getDetailMatchPhoto(Request $request): Response
    {
        $params = $request->all([
            'app_id',
            'book_id',
            'week_id',
            'practice_id',
            'type',
            'sample_id',
            'id'
        ]);

        //      todo check pass param type, sample_id để trả thêm dữ liệu
        return Inertia::render('SelectSample/DetailMatchPhoto', [
            'query' => $request->query(),
            'sample' => ''
        ]);
    }
}

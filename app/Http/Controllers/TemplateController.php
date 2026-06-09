<?php

namespace App\Http\Controllers;

use App\Enums\QuestionTemplateConstant;
use App\Helpers\Helper;
use App\Helpers\MediaHelper;
use App\Models\QuestionTemplate;
use App\Services\AppService;
use App\Models\App;
use App\Services\ExerciseService;
use App\Services\QuestionTemplateService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemplateController extends BaseModuleController
{
    protected $appService;
    protected $questionTemplateService;

    public function __construct(AppService $appService, QuestionTemplateService $questionTemplateService)
    {
        $this->appService = $appService;
        $this->questionTemplateService = $questionTemplateService;
    }


    public function index(Request $request)
    {
        $appId = $request->app_id;
        $app = $this->appService->findAppById($appId);
        $appList = App::get();
        if (!$app) {
            return abort(404);
        }
        return Inertia::render('Template/Index', [
            'query' => $request->query(),
            'appList' => $appList,
            'app' => $app
        ]);
    }

    public function listTemplate(Request $request, ExerciseService $exerciseService)
    {
        $template = $exerciseService->getList();

        return response()->json([
            'status' => true,
            'list' => $template['list'],
            'types' => $template['type_list']
        ]);
    }

    public function listTemplateApp(Request $request)
    {
        $appId = $request->app_id;
        $template = $this->questionTemplateService->findAppById($appId);

        return response()->json([
            'status' => true,
            'list' => $template['list'],
            'types' => $template['type_list']
        ]);
    }

    public function list(Request $request, ExerciseService $exerciseService)
    {
   
        $params = $request->all([
            'app_id',
            'search',
            'pageSize'
        ]);

        $list = $exerciseService->getListQuestionTemplate($params);

        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    public function store(Request $request, QuestionTemplateService $questionTemplateService)
    {
        $data = $request->all();
        $lastSort = QuestionTemplate::orderByDesc('sort_number')->value('sort_number');
        $data['sort_number'] = $lastSort ? $lastSort + 1000 : 1000;
        $questionTemplateService->store($data);

        return response()->json([
            'status' => true,
            'data' => [
                'redirectUrl' => route('templates.index', ['appId' => $data['app_id']])
            ]
        ]);
    }

    public function delete($id, Request $request)
    {
        $result = $this->questionTemplateService->delete($id);

        if (empty($result['status'])) {
            return response()->json([
                'status' => false,
                'messages' => $result['messages']
            ]);
        } else {
            return response()->json([
                'status' => true,
            ]);
        }
    }

    public function visible(Request $request, QuestionTemplateService $questionTemplateService)
    {
        $params = $request->all([
            'status',
            'id'
        ]);

        $questionTemplateService->updateVisible($params);

        return response()->json([
            'status' => true
        ]);
    }

    public function create(Request $request, AppService $appService)
    {
        $listApp = $appService->getListFull();

        return Inertia::render('Template/Create', [
            'query' => $request->query(),
            'listType' => QuestionTemplateConstant::type(),
            'listApp' => $listApp->toArray(),
        ]);
    }

    public function edit(Request $request, QuestionTemplateService $questionTemplateService, AppService $appService)
    {
        $record = $questionTemplateService->getDetail($request->get('question_template_id'));

        if ($record->image) {
            $record->image_show = Helper::getCloudFront($record->image);
        }

        $listApp = $appService->getListFull();

        $params = [
            'query' => $request->query(),
            'listType' => QuestionTemplateConstant::type(),
            'record' => $record,
            'listApp' => $listApp->toArray(),
        ];

        return Inertia::render('Template/Edit', $params);
    }

    public function detail(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $this->questionTemplateService->getDetail($request->get('question_template_id'))
        ]);
    }

    public function updatePosition(Request $request)
    {
        $fromId = $request->from_id;
        $toId = $request->to_id;
        $recordFrom = $this->questionTemplateService->getDetail($fromId);
        $recordTo = $this->questionTemplateService->getDetail($toId);
        $prevToId = $this->questionTemplateService->getPrevById($recordTo);
        $nextToId = $this->questionTemplateService->getNextById($recordTo);

        if ($recordFrom->sort_number < $recordTo->sort_number) {
            $this->questionTemplateService->updateNextPosition($recordFrom, $recordTo, $nextToId);
        } else {
            $this->questionTemplateService->updatePrevPosition($recordFrom, $recordTo, $prevToId);
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function copyData(Request $request) {
        $app_id = $request->app_id;
        $template_ids = $request->template_ids;
        if(!$app_id) {
            return response()->json([
                'status' => false,
                'message' => 'App id không được để trống'
            ]);
        }
        if(!$template_ids) {
            return response()->json([
                'status' => false,
                'message' => 'Vui lòng chọn template'
            ]);
        }
        foreach($template_ids as $template) { 
            $template_id = $template['id'];
            $questionTemplate = QuestionTemplate::where('id', $template_id)->first()->toArray();
            if(!empty($questionTemplate)) {
                QuestionTemplate::where('app_id', $app_id)->where('playable', $questionTemplate['playable'])->delete();
                $questionTemplateNew = $questionTemplate;
                unset($questionTemplateNew['id']);
                unset($questionTemplateNew['image_url']);
                unset($questionTemplateNew['created_at']);
                unset($questionTemplateNew['updated_at']);
                $questionTemplateNew['app_id'] = $app_id;
                QuestionTemplate::create($questionTemplateNew);
            }
        }
        return response()->json([
            'status' => true
        ]);
    }

    public function deleteMany(Request $request)
    {
        $template_ids = $request->template_ids;
        foreach($template_ids as $template) { 
            $template_id = $template['id'];
            QuestionTemplate::where('id', $template_id)->delete();
        }
        return response()->json([
            'status' => true
        ]);
    }
}

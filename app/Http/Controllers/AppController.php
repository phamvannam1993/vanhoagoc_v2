<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\UpdateAppRequest;
use App\Models\App;
use App\Services\AppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class AppController extends BaseModuleController
{
    protected $appService;

    public function __construct(AppService $appService)
    {
        $this->appService = $appService;
    }

    public function index(Request $request)
    {
        return Inertia::render('Dashboard', [
            'query' => $request->query(),
        ]);
    }

    public function jsonList(Request $request)
    {
        $params = $request->all();
        $list = $this->appService->getList($params, true);
        $list->loadCount('books');
        return response()->json([
            'status' => true,
            'data' => $list
        ]);
    }

    /**
     * Display the apps form.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('App/Create', [
            'query' => $request->query(),
        ]);
    }

    public function edit(Request $request): Response
    {
        $app = $this->appService->findAppById($request->id);
        $app->img_show = Helper::getCloudFront($app->img);
        $settingAdvance = $app->setting_advance ? json_decode($app->setting_advance, true) : null;

        if ($settingAdvance) {
            $background = json_decode($settingAdvance['background']);

            if (is_object($background)) {
                $background->value_show = $background->value_show ? Helper::getCloudFront($background->value_show) : null;
                $app->background = json_encode($background);
            } else {
                $app->background = null;
            }

            $app->background_sound = json_encode(json_decode($settingAdvance['background_sound']));
            $app->sound_click = json_encode(json_decode($settingAdvance['sound_click']));
            $app->sound_choose_correct = json_encode(json_decode($settingAdvance['sound_choose_correct']));
            $app->sound_choose_wrong = json_encode(json_decode($settingAdvance['sound_choose_wrong']));
            $app->sound_one_correct = json_encode(json_decode($settingAdvance['sound_one_correct']));
            $app->sound_one_wrong = json_encode(json_decode($settingAdvance['sound_one_wrong']));
            $app->sound_multi_correct = json_encode(json_decode($settingAdvance['sound_multi_correct']));
            $app->sound_multi_wrong = json_encode(json_decode($settingAdvance['sound_multi_wrong']));
            $app->sound_result_excellent = json_encode(json_decode($settingAdvance['sound_result_excellent']));
            $app->sound_result_good = json_encode(json_decode($settingAdvance['sound_result_good']));
            $app->sound_result_average = json_encode(json_decode($settingAdvance['sound_result_average']));
        }


        return Inertia::render('App/Edit', [
            'app' => $app,
            'query' => $request->query(),
        ]);
    }

    public function detail($id, Request $request): Response
    {
        $app = $this->appService->findAppById($id);

        return Inertia::render('App/Detail', [
            'app' => $app
        ]);
    }

    public function store(Request $request, AppService $appService)
    {
        $params = $request->all(['name', 'img']);
        $appId = $request->app_id;
        $validator = Validator::make($params, [
            'name' => 'required',
            'img' => 'nullable',
        ]);
        $params['sort_number'] = microtime(true) * 10000;

        if ($validator->fails()) {
            return response()->json([
                'messages' => $validator->errors()
            ]);
        } else {
            $appService->store($params);

            return response()->json([
                'status' => true,
                'data' => [
                    'redirectUrl' => route('apps.dashboard', [
                        'appId' => $appId
                    ])
                ]
            ]);
        }
    }

    public function update(Request $request, AppService $appService)
    {
        $params = $request->all();
        $params['img'] = $request->img ?? null;
        $settingAdvance = [
            'background' => $params['background'],
            'background_sound' => $params['background_sound'],
            'sound_click' => $params['sound_click'],
            'sound_choose_correct' => $params['sound_choose_correct'],
            'sound_choose_wrong' => $params['sound_choose_wrong'],
            'sound_one_correct' => $params['sound_one_correct'],
            'sound_one_wrong' => $params['sound_one_wrong'],
            'sound_multi_correct' => $params['sound_multi_correct'],
            'sound_multi_wrong' => $params['sound_multi_wrong'],
            'sound_result_excellent' => $params['sound_result_excellent'],
            'sound_result_good' => $params['sound_result_good'],
            'sound_result_average' => $params['sound_result_average'],
        ];
        $params['setting_advance'] = $settingAdvance;
        $appId = $request->appId;
        $validator = Validator::make($params, [
            'name' => 'required',
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'messages' => $validator->errors()
            ]);
        } else {
            $result = $appService->update($params);

            if (empty($result['status'])) {
                return response()->json([
                    'status' => false,
                    'messages' => $result['messages']
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => [
                        'redirectUrl' => route('apps.dashboard', [
                            'appId' => $appId
                        ])
                    ]
                ]);
            }
        }
    }

    public function delete($id, AppService $appService)
    {
        $appService->delete($id);

        return response()->json([
            'status' => true
        ]);
    }

    public function visible(Request $request, AppService $appService)
    {
        $params = $request->all([
            'status',
            'id'
        ]);

        $appService->updateVisible($params);

        return response()->json([
            'status' => true
        ]);
    }

    public function updatePosition(Request $request)
    {
        $fromId = $request->from_id;
        $toId = $request->to_id;
        $recordFrom = $this->appService->findAppById($fromId);
        $recordTo = $this->appService->findAppById($toId);
        $prevToId = $this->appService->getPrevById($recordTo);
        $nextToId = $this->appService->getNextById($recordTo);

        if ($recordFrom->sort_number < $recordTo->sort_number) {
            $this->appService->updateNextPosition($recordFrom, $recordTo, $nextToId);
        } else {
            $this->appService->updatePrevPosition($recordFrom, $recordTo, $prevToId);
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function clone(Request $request, AppService $appService)
    {
        $id = $request->get('id');

        $app = $appService->clone($id);

        return response()->json([
            'status' => true,
            'data' => [
                'redirectUrl' => route('apps.edit', [
                    'id' => $app->id
                ])
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\BaseModuleController;
use App\Services\StreakService;
use Illuminate\Http\Request;

class StreakController extends BaseModuleController
{
     public function __construct(
        private StreakService $streakService
    ){}

    public function getByUser(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required'
            ]);

            $data = $this->streakService->getCurrentByUser($request->all());

            return $this->successResponse($data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validateErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            Helper::logException($e);
            return $this->internalServerErrorResponse();
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required',
                'day_count' => 'nullable',
            ]);

            $data = $this->streakService->update($request->all());

            return $this->successResponse($data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validateErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            Helper::logException($e);
            return $this->internalServerErrorResponse();
        }
    }
}

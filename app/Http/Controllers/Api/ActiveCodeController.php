<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActiveCode;
use App\Services\ActiveCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActiveCodeController extends Controller
{
    protected $activeCodeService;

    public function __construct(ActiveCodeService $activeCodeService)
    {
        $this->activeCodeService = $activeCodeService;
    }


    public function create(Request $request) {
        try {
            $codes = $this->activeCodeService->generateCode(1);

            return response()->json(['status' => true, 'code' => data_get($codes[0], 'code')]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception->getMessage());
            Log::info($exception->getLine());

            return response()->json([
                'status' => false,
            ]);
        }

    }
}

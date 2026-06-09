<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActiveCode;
use App\Models\User;
use App\Helpers\SendMailHelper;
use App\Services\ActiveCodeService;
use Illuminate\Http\Request;
use App\Exports\ActiveCodeExport;
use App\Services\StudentService;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class ActiveCodeController extends Controller
{
    protected $activeCodeService;

    public function __construct(
        ActiveCodeService $activeCodeService,
        private StudentService $studentService
    )
    {
        $this->activeCodeService = $activeCodeService;
    }


    public function index(Request $request)
    {
        return Inertia::render('ActiveCode/Index', [
            'query' => $request->query(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('ActiveCode/Create', [
            'query' => $request->query(),
        ]);
    }

    public function save(Request $request) {
        $quantity = $request->quantity;
        $price = $request->price;
        $codes = $this->activeCodeService->generateCodeFromTool($quantity, 8);
        foreach($codes as $code) {
            $dataSave = [
                'code' => $code,
                'is_used' => 0,
                'price' => $price
            ];
            ActiveCode::create($dataSave);
        }
        return response()->json(['success' => true]);
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids');
            ActiveCode::whereIn('id', $ids)->delete();
            return response()->json([
                'status' => true
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function exportData(Request $request)
    {
        $params = $request->all();
        $active_codes = $this->activeCodeService->getList($params);
        return Excel::download(new ActiveCodeExport($active_codes), 'active_codes.xlsx');
    }

    public function jsonList(Request $request)
    {
        $params = $request->all();
        $paginator = $this->activeCodeService->getList($params);

        $result = $paginator->toArray();
        $items = $paginator->getCollection();
        $result['data'] = $items->map(function ($item) {
            $raw = $item->getRawOriginal('created_at');
            return [
                'id'         => $item->id,
                'code'       => $item->code,
                'price'      => $item->price,
                'is_used'    => $item->is_used,
                'source'     => $item->source == 1 ? 'Giỏ hàng' : 'Tạo thường',
                'created_at' => $raw
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $raw)
                        ->format('d/m/Y H:i')
                    : null,
            ];
        })->values()->all();

        return response()->json([
            'status' => true,
            'data'   => $result,
        ]);
    }

    function generateRandomCodes($quantity, $length = 8) {
        $codes = [];
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for ($i = 0; $i < $quantity; $i++) {
            $code = '';
            for ($j = 0; $j < $length; $j++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }
            $codes[] = $code;
        }
        return $codes;
    }

    public function sendMail(Request $request) {

        $request->validate([
            'phone' => 'required|string',
            'email' => 'required|string',
        ]);

        $price = $request->price;
        $email = $request->email;
        $package_name = $request->package_name;
        $name = $request->name;
        $packages = $request->packages ? $request->packages: [];
        $user = User::where('username', $request->phone)->first();
        $app_name = $request->app_name ? $request->app_name : 'App 3 Gốc';
        $isShowDefaultPassword = false;
        if (empty($user)) {
            $user = $this->studentService->createStudent([
                'email' => $email,
                'name' => $name,
                'tel' => $request->phone,
                'username' => $request->phone,
                'password' => '123456',
                'app_id' => 16,
                'user_type_id' => '6'
            ]);
            $isShowDefaultPassword = true;
        }
       
        $quantity = $request->quantity ? $request->quantity : 1;
        if(!empty($packages)) {
            for($i = 0; $i < count($packages); $i++) {
                $codes = $this->activeCodeService->generateRandomCodes($quantity, 8);
                foreach($codes as $code) {
                    $dataSave = [
                        'code' => $code,
                        'is_used' => 0,
                        'source' => 1,
                        'price' => $packages[$i]['price']
                    ];
                    ActiveCode::create($dataSave);
                }
                $packages[$i]['codes'] = implode('<br>', $codes);
            }
            $data = [
                'subject' => 'Gửi mã kích hoạt',
                'package_name' => $package_name,
                'app_name' => $app_name,
                'name' => $name,
                'packages' => $packages,
                'code' => "",
                'user' => $user,
                'isShowDefaultPassword' => $isShowDefaultPassword
            ];
            $html = view('emails.activation_combo', $data)->toHtml();
            $result = SendMailHelper::sendNow(
                $email,
                'Gửi mã kích hoạt',
                $html,
            );

            if ($result) {
                return response()->json(['success' => true, 'message' => 'Gửi mail thành công!']);
            }
            return response()->json(['success' => false, 'message' => 'Gửi mail thất bại']);
        }
        $codes = $this->activeCodeService->generateRandomCodes($quantity, 8);
        foreach($codes as $code) {
            $dataSave = [
                'code' => $code,
                'is_used' => 0,
                'source' => 1,
                'price' => $price
            ];
            ActiveCode::create($dataSave);
        }
        $codes = implode('<br>', $codes);
        if($price > 0) {
            $data = [
                'subject' => 'Gửi mã kích hoạt',
                'package_name' => $package_name,
                'app_name' => $app_name,
                'name' => $name,
                'code' => $codes,
                'user' => $user,
                'isShowDefaultPassword' => $isShowDefaultPassword
            ];
            $html = view('emails.activation', $data)->toHtml();
            $result = SendMailHelper::sendNow(
                $email,
                'Gửi mã kích hoạt',
                $html,
            );

            if ($result) {
                return response()->json(['success' => true, 'message' => 'Gửi mail thành công!']);
            }
            return response()->json(['success' => false, 'message' => 'Gửi mail thất bại']);
        } else {
            return response()->json(['success' => false]);
        }
    }
}

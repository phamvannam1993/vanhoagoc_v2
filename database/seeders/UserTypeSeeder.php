<?php

namespace Database\Seeders;

use App\Models\UserType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    public function run()
    {
        $userType = new UserType();
        $userType->name = 'Hiệu trưởng';
        $userType->url = 'director';
        $userType->status = 'on';
        $userType->type = 'director';
        $userType->created_at = Carbon::now();
        $userType->updated_at = Carbon::now();
        $userType->save();
    }
}

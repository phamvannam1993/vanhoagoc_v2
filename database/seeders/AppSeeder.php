<?php

namespace Database\Seeders;

use App\Models\App;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSeeder extends Seeder
{
    public function run()
    {
        $apps = App::all();
        foreach ($apps as $key => $app) {
            DB::table('app')->where('id', $app->id)->update(['sort_number' => microtime(true)*10000]);
            if ($key > 0) {
                sleep(3);
            }
        }
    }
}

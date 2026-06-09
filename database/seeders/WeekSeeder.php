<?php

namespace Database\Seeders;

use App\Models\Week;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeekSeeder extends Seeder
{
    public function run()
    {
        $weeks = Week::whereNull('sort_number')->get();
        foreach ($weeks as $key => $week) {
            DB::table('week')->where('id', $week->id)->update(['sort_number' => microtime(true)*10000]);
            if ($key > 0) {
                sleep(3);
            }
        }
    }
}

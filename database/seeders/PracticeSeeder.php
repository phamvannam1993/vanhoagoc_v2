<?php

namespace Database\Seeders;

use App\Models\Practice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PracticeSeeder extends Seeder
{
    public function run()
    {
        $batchSize = 100;
        $counter = 1;
        $step = 1000;
        $start = now()->timestamp * 1000;

        Practice::orderBy('id')->whereNull('sort_number')->chunk($batchSize, function ($practices) use (&$counter, $step, $start) {
            foreach ($practices as $practice) {
                $practice->sort_number = $start + ($counter * $step);
                $practice->save();
                $counter++;
            }
        });
    }
}

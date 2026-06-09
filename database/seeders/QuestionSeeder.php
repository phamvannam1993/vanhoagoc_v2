<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        $batchSize = 100;
        $counter = 1;
        $step = 1000;
        $start = now()->timestamp * 1000;

        Question::orderBy('id')->whereNull('sort_number')->chunk($batchSize, function ($questions) use (&$counter, $step, $start) {
            foreach ($questions as $question) {
                $question->sort_number =  $start + ($counter * $step);
                $question->save();
                $counter++;
            }
        });
    }
}

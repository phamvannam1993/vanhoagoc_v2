<?php

namespace Database\Seeders;

use App\Models\QuestionEditor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionEditorSeeder extends Seeder
{
    public function run()
    {
        $batchSize = 100;
        $counter = 1;
        $step = 1000;
        $start = now()->timestamp * 1000;

        QuestionEditor::orderBy('id')->whereNull('sort_number')->chunk($batchSize, function ($questionEditors) use (&$counter, $step, $start) {
            foreach ($questionEditors as $questionEditor) {
                $questionEditor->sort_number = $start + ($counter * $step);
                $questionEditor->save();
                $counter++;
            }
        });
    }
}

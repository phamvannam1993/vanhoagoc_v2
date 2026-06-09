<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;

class QuestionEditorsImport implements ToModel
{
    public function model($row)
    {
        echo '<pre>';
        print_r($row);
        echo '</pre>';
        exit();
    }
}

<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public $data = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!$row->filter()->isEmpty()) {
                $this->data[] = $row->toArray();
            }
        }
    }
}

<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class ActiveCodeExport implements FromView
{
    protected $active_codes;

    public function __construct($active_codes)
    {
        $this->active_codes = $active_codes;
    }

    public function view(): View
    {
        return view('exports.active_codes', [
            'active_codes' => $this->active_codes
        ]);
    }
}

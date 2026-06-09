<?php

namespace App\Exports;

use App\Models\User;
use App\Repositories\EventPointRepository;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class EventRanking implements FromView
{
    protected $eventPointRepository;

    public function __construct(private String $eventId)
    {
        $this->eventPointRepository = app(EventPointRepository::class);
    }

    public function view(): View
    {
        $entities = $this->eventPointRepository->getRanking(['event_id' => $this->eventId], false, ['user']);

        return view('exports.event_ranking', [
            'entities' => $entities
        ]);
    }
}

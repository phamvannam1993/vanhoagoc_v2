<?php

namespace App\Repositories;

use App\Models\VoiceType;

class VoiceTypeRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return VoiceType::class;
    }

    public function getVoiceTypes() {
        return VoiceType::select('id', 'name', 'type')->get();
    }
}

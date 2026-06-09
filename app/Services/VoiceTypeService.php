<?php

namespace App\Services;

use App\Repositories\VoiceTypeRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class VoiceTypeService
{
    public function __construct(
        private VoiceTypeRepository $voiceTypeRepository,
    ) {}
}

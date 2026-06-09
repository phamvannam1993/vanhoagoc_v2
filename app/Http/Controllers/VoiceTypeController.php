<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\VoiceTypeRepository;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Storage;
use App\Services\VoiceTypeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class VoiceTypeController extends Controller
{
    private $voiceTypeRepository;
    private $voiceTypeService;

    public function __construct(VoiceTypeRepository $voiceTypeRepository, VoiceTypeService $voiceTypeService)
    {
        $this->voiceTypeRepository = $voiceTypeRepository;
        $this->voiceTypeService = $voiceTypeService;
    }

    public function list()
    {
        try {
            $voiceTypes = $this->voiceTypeRepository->getVoiceTypes();
            return response()->json([
                'success' => true,
                'data' => $voiceTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function textToSpeech(Request $request)
    {
        $user = Auth::user();
        // Validate input
        $request->validate([
            'text' => 'required|string',
            'voice_type' => 'required|string',
        ]);

        $text = $request->text;
        $voice = $request->voice_type;

        $data = [
            'app_id' => env('VBEE_APP_ID'),
            'response_type' => 'direct',
            'callback_url' => route('voice-types.vbee.callback'),
            'input_text' => $text,
            'voice_code' => $voice,
            'audio_type' => 'mp3',
            'bitrate' => 128,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('VBEE_API_TOKEN')
        ])->timeout(1200)->post('https://vbee.vn/api/v1/tts', $data);

        if ($response->successful()) {
            $result = $response->json();

            $get_url = $result['result']['audio_link'];
            $audioContent = file_get_contents($get_url);
            $filename = 'ai_audio/' . uniqid('audio_', true) . '.mp3';
            Storage::disk('s3')->put($filename, $audioContent);

            $url = Helper::getCloudFront($filename);
            return response()->json(['success' => true, 's3_url' => $url, 'url' => $filename]);
        } else {
            return response()->json(['success' => false, 'message' => 'Failed to convert text to speech'], 500);
        }
    }
}

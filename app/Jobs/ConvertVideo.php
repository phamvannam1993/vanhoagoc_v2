<?php

namespace App\Jobs;

use App\Enums\PracticeConstant;
use App\Models\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class ConvertVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $table;
    private $id;
    private $path;
    private $option;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($table, $id, $path, $option = [])
    {
        $this->table = $table;
        $this->id = $id;
        $this->path = $path;
        $this->option = $option;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $absolutedPath = public_path($this->path);
        $convertedPath = storage_path('app/' . $this->path);

        $s3FilePath = $this->path;

        Log::info('Starting video conversion for file: ' . $absolutedPath);

        if (file_exists($absolutedPath)) {
            $info = pathinfo($convertedPath);
            $outputDir = $info['dirname'];
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            try {
                set_time_limit(0);
                $command = [
                    'ffmpeg',
                    '-y', // replace exist file
                    '-i',
                    $absolutedPath,
                    '-c',
                    'copy',
                    '-movflags',
                    'faststart',
                    $convertedPath
                ];

                Log::info('FFmpeg command: ' . implode(' ', $command));

                $process = new Process($command);
                $process->setTimeout(0);
                $process->mustRun();

                // check converted file for exist
                if (!file_exists($convertedPath)) {
                    Log::info('FFmpeg command Failed: ' . implode(' ', $command));

                    return 0;
                }

                // Stream file into s3
                $fileStream = Storage::disk('local')->readStream($this->path);
                Storage::disk('s3')->put($s3FilePath, $fileStream);

                // update database
                switch ($this->table) {
                        // case 'video_live':
                        //     M_video_live::where('id', $this->id)
                        //         ->update(['is_converted' => 1]);

                        //     break;
                        // case 'question_editor':
                        //     switch ($this->option['field']) {
                        //         case 'question_video_url':
                        //             $isConVert = M_question_editor::IS_CONVERTED_QUESTION_VIDEO_URL;
                        //             break;
                        //     }
                        //     $record = M_question_editor::find($this->id);
                        //     if (!empty($record->id)) {
                        //         $record->is_converted = $record->is_converted | $isConVert;
                        //         $record->save();
                        //     }

                        //     break;
                    case 'practice':
                        switch ($this->option['field']) {
                            case 'lesson_video':
                                $isConVert = PracticeConstant::IS_CONVERTED_PRACTICE_VIDEO_URL;
                                break;
                            case 'lesson_noi':
                                $isConVert = PracticeConstant::IS_CONVERTED_PRACTICE_NOI_URL;
                                break;
                        }

                        $record = Practice::find($this->id);
                        if (!empty($record->id)) {
                            $record->is_converted = $record->is_converted | $isConVert;
                            $record->save();
                        }

                        break;
                }

                // delete local file
                unlink($absolutedPath);
                unlink($convertedPath);
            } catch (\Exception $e) {
                Log::error('Chuyển đổi video thất bại: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        } else {
            Log::error('Tệp nguồn không tồn tại: ' . $absolutedPath);
        }

        return 1;
    }
}

<?php

namespace App\Services\Traits;

use Illuminate\Support\Facades\Storage;

trait videoManagerTrait
{
    public function uploadVideo($file, $mediaPath)
    {
        $extend = $file->getClientOriginalExtension();

        if (!in_array($extend, $this->fileAcceptType())) {
            return [
                'status' => false,
                'messages' => [
                    'video' => 'Lỗi, Bạn chỉ được chọn file ảnh có đuôi là .mp3 .mp4 (phân biệt viết hoa và viết thường)'
                ]
            ];
        }

        $video = $file->getClientOriginalName();
        $mediaPath = $mediaPath;

        $i = 1;
        while (Storage::disk(config('filesystems.media'))->exists($mediaPath . $video)) {
            if ($i == 1) {
                $video = str_replace('.', '-' . $i++ . '.', $video);
            } else {
                $a = $i - 1;
                $video = str_replace($a . '.', $i++ . '.', $video);
            }
        }

        Storage::disk(config('filesystems.media'))->putFileAs($mediaPath, $file, $video);

        return [
            'status' => true,
            'video' => $video
        ];
    }

    public function deleteFile($media_path, $video)
    {
        Storage::disk(config('filesystems.media'))->delete($media_path . $video);
    }

    public function fileAcceptType()
    {
        return [
            'mp4',
            'MP4',
            'mp3',
            'MP3'
        ];
    }
}

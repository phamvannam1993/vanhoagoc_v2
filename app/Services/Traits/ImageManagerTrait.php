<?php

namespace App\Services\Traits;

use Illuminate\Support\Facades\Storage;

trait ImageManagerTrait
{
    public function uploadImg($file, $mediaPath)
    {
        $extenImg = $file->getClientOriginalExtension();

        if (!in_array($extenImg, $this->fileAcceptType())) {
            return [
                'status' => false,
                'messages' => [
                    'image' => 'Lỗi, Bạn chỉ được chọn file ảnh có đuôi là .jpg, .png, .jpeg (phân biệt viết hoa và viết thường)'
                ]
            ];
        }

        $img = $file->getClientOriginalName();
        $mediaPath = $mediaPath;

        $i = 1;
        while (Storage::disk(config('filesystems.media'))->exists($mediaPath . $img)) {
            if ($i == 1) {
                $img = str_replace('.', '-' . $i++ . '.', $img);
            } else {
                $a = $i - 1;
                $img = str_replace($a . '.', $i++ . '.', $img);
            }
        }

        Storage::disk(config('filesystems.media'))->putFileAs($mediaPath, $file, $img);

        return [
            'status' => true,
            'img' => $img
        ];
    }

    public function deleteFile($media_path, $img)
    {
        Storage::disk(config('filesystems.media'))->delete($media_path . $img);
    }

    public function fileAcceptType()
    {
        return [
            'pdf',
            'PDF',
            'docx',
            'doc',
            'webp',
            'jpg',
            'png',
            'jpeg',
            'JPEG',
            'PNG',
            'JPG'
        ];
    }
}

<?php

namespace App\Services;

use App\Repositories\BookRepository;
use App\Repositories\PracticeImageRepository;
use App\Repositories\PracticeRepository;
use App\Repositories\WeekRepository;
use Illuminate\Support\Facades\Storage;

class ReadingService
{
    private $practiceRepository;
    private $weekRepository;
    private $bookRepository;

    public function __construct(
        PracticeRepository $practiceRepository,
        WeekRepository $weekRepository,
        BookRepository $bookRepository,
        private PracticeImageRepository $practiceImageRepository,
        private FileService $fileService
    ) {
        $this->practiceRepository = $practiceRepository;
        $this->weekRepository = $weekRepository;
        $this->bookRepository = $bookRepository;
    }

    public function getDetail($id)
    {
        return $this->practiceRepository->findByFilter([
            'id' => $id
        ]);
    }

    public function uploadImage($practiceId, $images, $removeEntities)
    {
        if(!empty($images)){
            $paths = $this->fileService->handleUploadFile($images, 'practices/');

            foreach ($paths as $path) {
                $practiceImages[] = [
                    'practice_id' => $practiceId,
                    'path' => $path,
                    'created_at' => now()
                ];
            }
            $this->practiceImageRepository->insert($practiceImages);
        }

        if(!empty($removeEntities)){

            foreach ($removeEntities as $entity) {
                $ids[] = $entity['id'];
                Storage::disk(config('filesystems.storage_disk'))->delete($entity['path']);
            }

            if (!empty($ids)) {
                $this->practiceImageRepository->whereIn('id', $ids)->delete();
            }
        }
    }
}

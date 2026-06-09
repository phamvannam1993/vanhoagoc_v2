<?php

namespace App\Services;

use App\Enums\PracticeConstant;
use App\Helpers\BinaryHelper;
use App\Repositories\BookRepository;
use App\Repositories\PracticeRepository;
use App\Repositories\WeekRepository;
use App\Services\Traits\videoManagerTrait;

class VideoService
{
    use videoManagerTrait;

    private $practiceRepository;
    private $weekRepository;
    private $bookRepository;

    public function __construct(
        PracticeRepository $practiceRepository,
        WeekRepository $weekRepository,
        BookRepository $bookRepository
    ) {
        $this->practiceRepository = $practiceRepository;
        $this->weekRepository = $weekRepository;
        $this->bookRepository = $bookRepository;
    }

    public function store($data, $file)
    {
        if (empty($file)) {
            return [];
        } else {
            $record = $this->practiceRepository->first([
                'id' => $data['id']
            ]);

            if (!empty($record->id)) {
                $updatedField = $data['field'];

                if (!empty($record->{$updatedField})) {
                    $this->deleteFile(PracticeConstant::MEDIA_PATH, $record->{$updatedField});
                }

                $result = $this->uploadVideo($file, PracticeConstant::MEDIA_PATH);

                if (empty($result['status'])) {
                    return $result;
                } else {
                    $record->{$updatedField} = $result['video'];
                }

                switch ($data['field']) {
                    case 'lesson_video':
                        $isConvert = BinaryHelper::compareBinary($record->is_converted, PracticeConstant::IS_CONVERTED_PRACTICE_VIDEO_URL) ? PracticeConstant::IS_CONVERTED_PRACTICE_VIDEO_URL : 0;
                        break;
                    case 'lesson_noi':
                        $isConvert = BinaryHelper::compareBinary($record->is_converted, PracticeConstant::IS_CONVERTED_PRACTICE_NOI_URL) ? PracticeConstant::IS_CONVERTED_PRACTICE_NOI_URL : 0;
                        break;
                }

                $record->is_converted = $isConvert;
                $record->save();

                // convert video job btodo
                // $option = [
                //     'field' => $updatedField
                // ];
                // ConvertVideo::dispatch('practice', $record->id, PracticeConstant::MEDIA_PATH . $result['video'], $option);
            }
        }
    }

    public function getDetail($id)
    {
        return $this->practiceRepository->findByFilter([
            'id' => $id
        ]);
    }
}

<?php

namespace App\Services;

use App\Enums\QuestionTemplateConstant;
use App\Helpers\Helper;
use App\Repositories\QuestionTemplateRepository;

class QuestionTemplateService
{
    private $questionTemplateRepository;

    public function __construct(
        QuestionTemplateRepository $questionTemplateRepository
    ) {
        $this->questionTemplateRepository = $questionTemplateRepository;
    }

    public function store($data)
    {
        $info = [
            'app_id' => $data['app_id'],
            'type' => $data['type'],
            'type_label' => $data['type_label'],
            'image' => $data['image_db'],
            'playable' => $data['code'],
            'status' => empty($data['status']) ? QuestionTemplateConstant::OFF : QuestionTemplateConstant::ON,
            'template' => $data['template'],
            'sort_number' => $data['sort_number']
        ];

        if (empty($data['id'])) {
            $record = $this->questionTemplateRepository->create($info);
        } else {
            $record = $this->questionTemplateRepository->first([
                'id' => $data['id']
            ]);

            $record->fill($info);

            $record->save();
        }

        return $record;
    }

    public function updateVisible($data)
    {
        $this->questionTemplateRepository->updateByFilters([
            'id' => $data['id']
        ], [
            'status' => $data['status'] == QuestionTemplateConstant::ON ? QuestionTemplateConstant::ON : QuestionTemplateConstant::OFF
        ]);
    }

    public function getDetail($id)
    {
        return $this->questionTemplateRepository->first([
            'id' => $id
        ]);
    }

    public function delete($id)
    {
        $record = $this->questionTemplateRepository->first([
            'id' => $id
        ]);

        $this->questionTemplateRepository->deleteByFilter([
            'id' => $id
        ]);

        return [
            'status' => true
        ];
    }

    public function findAppById($appId)
    {
        $list = $this->questionTemplateRepository->getByApp($appId);
        $types = [];

        foreach ($list as $item) {
            $item->image_url = Helper::getCloudFront($item->image);
            if (empty($types[$item->type])) {
                $types[$item->type] = [
                    'value' => $item->type,
                    'label' => $item->type_label
                ];
            }
        }

        return [
            'list' => $list,
            'type_list' => array_values($types)
        ];
    }

    public function getPrevById($recordTo)
    {
        return $this->questionTemplateRepository->getPrevById($recordTo);
    }
    public function getNextById($recordTo)
    {
        return $this->questionTemplateRepository->getNextById($recordTo);
    }

    public function updateNextPosition($recordFrom, $recordTo, $nextToId)
    {
        if (!$nextToId) {
            $this->questionTemplateRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => microtime(true) * 10000
            ]);
        } else {
            $this->questionTemplateRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $nextToId->sort_number) / 2
            ]);
        }
    }
    public function updatePrevPosition($recordFrom, $recordTo, $prevToId)
    {
        if (!$prevToId) {
            $this->questionTemplateRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => $recordTo->sort_number - 1
            ]);
        } else {
            $this->questionTemplateRepository->updateByFilters([
                'id' => $recordFrom->id
            ], [
                'sort_number' => ($recordTo->sort_number + $prevToId->sort_number) / 2
            ]);
        }
    }
}

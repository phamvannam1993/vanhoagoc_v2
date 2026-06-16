<?php

namespace App\Services\Admin\Class;

use App\Models\UserClass;
use App\Repositories\ClassesRepository;
use App\Repositories\UserClassRepository;

class ClassService
{
    private $classesRepository;
    private $userClassRepository;

    public function __construct(
        ClassesRepository $classesRepository, UserClassRepository $userClassRepository

    ) {
        $this->classesRepository = $classesRepository;
        $this->userClassRepository = $userClassRepository;
    }

    public function getList($data)
    {
        $users = [];
        if (!empty($data['app_id'])) {
            $users = ['users' => function($q) use($data){
                $usersClassesTable = UserClass::query()->getModel()->getTable();
                return $q->where("$usersClassesTable.app_id", $data['app_id']);
            }];
        }

        return $this->classesRepository->searchByFilters($data, [], $users);
    }

    public function store($data)
    {
        $info = [
            'app_id' => $data['app_id'],
            'name' => $data['name'],
            'year' => $data['year']
        ];

        if (empty($data['id'])) {
            $record = $this->classesRepository->create($info);
        } else {
            $record = $this->classesRepository->first([
                'id' => $data['id']
            ]);

            $record->fill($info);

            $record->save();
        }

        return $record;
    }

    public function detail($request)
    {
        return $this->classesRepository->first(['id' => $request['id'], 'app_id' => $request['app_id']]);
    }

    public function getClassByApp($appId)
    {
        return $this->classesRepository->getClassByApp($appId);
    }

    public function getStudentByClass($classIds)
    {
        return $this->userClassRepository->getStudentByClass($classIds);
    }
}

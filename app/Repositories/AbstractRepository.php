<?php

namespace App\Repositories;

use Closure;

abstract class AbstractRepository
{
    public const pagingItem = 20;
    public $model;

    abstract public function getModelClass();

    public function __construct()
    {
        $this->makeModel();
    }

    public function getModel()
    {
        return $this->model;
    }

    public function makeModel()
    {
        $model = app($this->getModelClass());

        return $this->model = $model;
    }

    public function first($filters, $options = [])
    {
        $query = $this->model->where($filters);

        if (!empty($options['with'])) {
            foreach ($options['with'] as $with) {
                $query->with($with);
            }
        }

        return $query->first();
    }

    public function create($fields)
    {
        return $this->model->create($fields);
    }

    public function all()
    {
        $result = $this->model->all();

        return $result;
    }

    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->model->findOrFail($id, $columns);

        return  $result;
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        $result = $this->model->updateOrCreate($attributes, $values);
        return  $result;
    }

    public function findByFilter($filters, $moreCondition = [])
    {
        $query =  $this->model->where($filters);

        if (!empty($moreCondition['order'])) {
            $query->orderByRaw($moreCondition['order']);
        }

        if (!empty($moreCondition['except'])) {
            foreach ($moreCondition['except'] as $field => $value) {
                $query->where($field, '<>', $value);
            }
        }

        if (!empty($moreCondition['with'])) {
            foreach ($moreCondition['with'] as $relation) {
                $query->with($relation);
            }
        }

        return $query->first();
    }

    public function find($id, $columns = ['*'], $limit = 3)
    {
        $result = $this->model->find($id, $columns);

        return  $result;
    }

    public function get($filters, $options = [])
    {
        $query =  $this->model->where($filters);

        if (!empty($options['orderBy'])) {
            $query->orderByRaw($options['orderBy']);
        }

        if (!empty($options['groupBy'])) {
            $query->groupByRaw($options['groupBy']);
        }

        if (!empty($options['with'])) {
            foreach ($options['with'] as $with) {
                $query->with($with);
            }
        }

        if (!empty($options['withArr'])) {
            $query->with($options['withArr']);
        }

        return $query->get();
    }

    public function simplePaging($filters)
    {
        $result = $this->model->where($filters)->paginate(self::pagingItem);

        return $result;
    }

    public function insert($array)
    {
        $this->model->insert($array);
    }

    public function updateByFilters($filters, $updates)
    {
        $this->model->where($filters)
            ->update($updates);
    }

    public function deleteByFilter($filters)
    {
        $result =  $this->model->where($filters)->delete();

        return $result;
    }

    public function __call($method, $arguments)
    {
        return $this->getModel()->$method(...$arguments);
    }
}

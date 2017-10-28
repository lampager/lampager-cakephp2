<?php

App::uses('Model', 'Model');

class LampagerColumnAccess
{
    /** @var Model */
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function has(array $data, $column)
    {
        if (strpos($column, '.')) {
            list($model, $column) = explode('.', $column);
            return isset($data[$model][$column]) || isset($data["{$model}.{$column}"]);
        }
        return isset($data[$this->model->alias][$column]) || isset($data[$column]);
    }

    public function get(array $data, $column)
    {
        if (!$this->has($data, $column)) {
            return null;
        }
        if (strpos($column, '.')) {
            list($model, $column) = explode('.', $column);
            if (isset($data[$model][$column])) {
                return $data[$model][$column];
            }
            return $data["{$model}.{$column}"];
        }
        if (isset($data[$this->model->alias][$column])) {
            return $data[$this->model->alias][$column];
        }
        return $data[$column];
    }

    public function with($column, $value)
    {
        if (strpos($column, '.')) {
            list($model, $column) = explode('.', $column);
            return [
                $model => [
                    $column => $value,
                ],
            ];
        }
        return [
            $this->model->alias => [
                $column => $value,
            ],
        ];
    }
}

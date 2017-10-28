<?php

App::uses('Model', 'Model');

use Lampager\ArrayProcessor;
use Lampager\Query;

class LampagerArrayProcessor extends ArrayProcessor
{
    /** @var Model */
    protected $model;

    /**
     * @return static
     */
    public static function create(Model $model)
    {
        return new static($model);
    }

    /**
     * @param Model $model Model.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function field($row, $column)
    {
        if (strpos($column, '.')) {
            list($model, $column) = explode('.', $column);
            if (isset($row[$model][$column])) {
                return $row[$model][$column];
            }
            return $row["{$model}.{$column}"];
        }
        if (isset($row[$this->model->alias][$column])) {
            return $row[$this->model->alias][$column];
        }
        return $row["{$this->model->alias}.{$column}"];
    }
}

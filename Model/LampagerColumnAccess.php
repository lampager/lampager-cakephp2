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

    /**
     * Return a value indicating whether the data has any value whose field mathes the column.
     *
     * @param  string $column
     * @return bool
     */
    public function has(array $data, $column)
    {
        if (strpos($column, '.')) {
            list($model, $column) = explode('.', $column);
            return isset($data[$model][$column]) || isset($data["{$model}.{$column}"]);
        }
        return isset($data[$this->model->alias][$column]) || isset($data["{$this->model->alias}.{$column}"]) || isset($data[$column]);
    }

    /**
     * Get a value from the data by the column.
     *
     * @param  string $column
     * @return mixed
     */
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
        if (isset($data["{$this->model->alias}.{$column}"])) {
            return $data["{$this->model->alias}.{$column}"];
        }
        return $data[$column];
    }

    /**
     * Create an associative array which has model as 1st dimensional key and column as 2nd dimensional key.
     *
     * @param  string    $column
     * @param  mixed     $value
     * @return mixed[][]
     */
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

    /**
     * Iterate through the data with flatten field name
     *
     * @return \Generator
     */
    public function iterate(array $data)
    {
        foreach ($data as $model => $value) {
            if (strpos($model, '.')) {
                yield $model => $value;
                continue;
            }
            foreach ($value as $column => $v) {
                yield "{$model}.{$column}" => $v;
            }
        }
    }
}

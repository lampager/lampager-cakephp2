<?php
App::uses('Model', 'Model');

use Lampager\Cursor;

class LampagerArrayCursor implements Cursor
{
    /** @var array */
    protected $cursor;

    /** @var Model */
    protected $model;

    public function __construct(array $cursor = [], Model $model)
    {
        $this->cursor = $cursor;
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function has($column)
    {
        if (strpos($column, '.')) {
            list($model, $column) = explode('.', $column);
            return isset($this->cursor[$model][$column]) || isset($this->cursor["{$model}.{$column}"]);
        }
        return isset($this->cursor[$this->model->alias][$column]) || isset($this->cursor[$column]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($column)
    {
        if (!$this->has($column)) {
            return null;
        }
        if (strpos($column, '.')) {
            list($model, $column) = explode('.', $column);
            if (isset($this->cursor[$model][$column])) {
                return $this->cursor[$model][$column];
            }
            return $this->cursor["{$model}.{$column}"];
        }
        if (isset($this->cursor[$this->model->alias][$column])) {
            return $this->cursor[$this->model->alias][$column];
        }
        return $this->cursor[$column];
    }
}

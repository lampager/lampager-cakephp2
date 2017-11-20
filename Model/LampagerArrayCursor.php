<?php

App::uses('Model', 'Model');
App::uses('LampagerColumnAccess', 'Lampager.Model');

use Lampager\Contracts\Cursor;

class LampagerArrayCursor implements Cursor
{
    /** @var array */
    protected $cursor;

    /** @var LampagerColumnAccess */
    protected $access;

    public function __construct(Model $model, array $cursor = [])
    {
        $this->cursor = $cursor;
        $this->access = new LampagerColumnAccess($model);
    }

    /**
     * {@inheritdoc}
     */
    public function has(...$columns)
    {
        if (empty($this->cursor)) {
            return null;
        }
        foreach ($columns as $column) {
            if (!$this->access->has($this->cursor, $column)) {
                return false;
            }
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($column)
    {
        return $this->access->get($this->cursor, $column);
    }
}

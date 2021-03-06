<?php

App::uses('Model', 'Model');
App::uses('LampagerColumnAccess', 'Lampager.Model');

use Lampager\ArrayProcessor;
use Lampager\Query;
use Lampager\Query\Order;

class LampagerArrayProcessor extends ArrayProcessor
{
    /** @var Model */
    protected $model;

    /** @var LampagerColumnAccess */
    protected $access;

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
        $this->access = new LampagerColumnAccess($model);
    }

    /**
     * {@inheritdoc}
     */
    protected function field($row, $column)
    {
        return $this->access->get($row, $column);
    }

    /**
     * {@inheritdoc}
     */
    protected function makeCursor(Query $query, $row)
    {
        return array_replace_recursive(
            ...array_map(
                function ($column) use ($row) {
                    return $this->access->with($column, $this->field($row, $column));
                },
                array_intersect(
                    array_keys(
                        iterator_to_array($this->access->iterate($row))
                    ),
                    array_map(
                        static function (Order $order) {
                            return $order->column();
                        },
                        $query->orders()
                    )
                )
            )
        );
    }
}

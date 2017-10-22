<?php
App::uses('Model', 'Model');

use Lampager\ArrayProcessor;
use Lampager\Query\Query;

class LampagerArrayProcessor extends ArrayProcessor
{
    /** @var Model */
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Make a cursor from the specific row.
     *
     * @param  Query          $query
     * @param  mixed          $row
     * @return int[]|string[]
     */
    protected function makeCursor(Query $query, $row)
    {
        $fields = [];
        foreach ($query->orders() as $order) {
            if (strpos($order->column(), '.') !== false) {
                list($model, $column) = explode('.', $order->column());
                $fields[$model][$column] = $this->field($row[$model], $column);
                continue;
            }
            if (isset($row[$this->model->alias][$order->column()])) {
                list($model, $column) = [$this->model->alias, $order->column()];
                $fields[$model][$column] = $this->field($row[$model], $column);
                continue;
            }
            $fields[$order->column()] = $this->field($row, $order->column());
        }
        return $fields;
    }
}

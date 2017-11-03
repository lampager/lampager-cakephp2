<?php

// @codeCoverageIgnoreStart
App::uses('Model', 'Model');
App::uses('LampagerTransformer', 'Lampager.Model');
// @codeCoverageIgnoreEnd

use Lampager\Paginator as BasePaginator;
use Lampager\Query\Order;

class LampagerPaginator extends BasePaginator
{
    /** @var Model */
    public $builder;

    /** @var array */
    public $query;

    /** @var LampagerTransformer */
    public $transformer;

    public function __construct(Model $builder, array $query)
    {
        $this->builder = $builder;
        $this->fromArray($query);
        $this->transformer = new LampagerTransformer($this);
    }

    /**
     * @param  Model             $builder Model.
     * @param  array             $query   Query.
     * @return static
     */
    public static function create(Model $builder, array $query)
    {
        return new static($builder, $query);
    }

    /**
     * Add cursor parameter name for ORDER BY statement.
     *
     * @param  string|int $column
     * @param  string     $order
     * @return $this
     */
    public function orderBy($column, $order = Order::ASC)
    {
        if (is_int($column)) {
            list($column, $order) = explode(' ', $order) + [1 => 'ASC'];
        }
        if (strpos($column, '.') === false) {
            $column = "{$this->builder->alias}.{$column}";
        }
        return parent::orderBy($column, strtolower($order));
    }

    /**
     * Define options from an associative array.
     *
     * @param  (bool|int|string[])[] $options
     * @return $this
     */
    public function fromArray(array $options)
    {
        // Not supported in CakePHP 2 version
        unset($options['orders']);

        // Merge with existing query
        $this->query = array_replace_recursive($this->query ?: [], $options);

        if (isset($options['order'])) {
            foreach ($options['order'] as $column => $order) {
                $this->orderBy($column, $order);
            }
        }

        return parent::fromArray($options);
    }
}

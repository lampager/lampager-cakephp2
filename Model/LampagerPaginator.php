<?php

App::uses('Model', 'Model');
App::uses('LampagerTransformer', 'Lampager.Model');

use Lampager\Paginator as BasePaginator;

class LampagerPaginator extends BasePaginator
{
    /** @var Model */
    public $builder;

    /** @var array */
    public $query;

    /** @var LampagerTransformer */
    public $transformer;

    /** @var string[] */
    protected static $mapToOptions = [
        'order',
        'limit',
        'forward',
        'backward',
        'exclusive',
        'inclusive',
        'seekable',
        'unseekable',
    ];

    public function __construct(Model $builder, array $query)
    {
        $this->builder = $builder;
        $this->query = $query;
        $this->transformer = new LampagerTransformer($this);
    }

    /**
     * @param  Model             $builder Model.
     * @param  array             $query   Query.
     * @return LampagerPaginator
     */
    public static function fromQuery(Model $builder, array $query)
    {
        $paginator = new static($builder, $query);

        foreach (static::$mapToOptions as $key) {
            if (isset($query[$key])) {
                $paginator->$key($query[$key]);
            }
        }

        return $paginator;
    }

    /**
     * Map order options to Paginator.
     * @param  string[] $orders Orders.
     * @return $this
     */
    protected function order(array $orders)
    {
        foreach ($orders as $column => $order) {
            if (is_int($column)) {
                list($column, $order) = explode(' ', $order) + [1 => 'ASC'];
            }
            if (strpos($column, '.') === false) {
                $column = "{$this->builder->alias}.{$column}";
            }
            $this->orderBy($column, strtolower($order));
        }
        return $this;
    }
}

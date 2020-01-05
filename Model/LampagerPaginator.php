<?php

App::uses('Model', 'Model');
App::uses('LampagerArrayCursor', 'Lampager.Model');
App::uses('LampagerArrayProcessor', 'Lampager.Model');
App::uses('LampagerTransformer', 'Lampager.Model');

use Lampager\ArrayProcessor;
use Lampager\PaginationResult;
use Lampager\Paginator as BasePaginator;
use Lampager\Query;
use Lampager\Query\Order;

class LampagerPaginator extends BasePaginator
{
    /** @var Model */
    public $builder;

    /** @var array */
    public $options;

    /** @var ArrayProcessor */
    public $processor;

    /** @var LampagerTransformer */
    public $transformer;

    public function __construct(Model $builder, array $options)
    {
        $this->builder = $builder;
        $this->fromArray($options);

        $this->processor = new LampagerArrayProcessor($builder);
        $this->transformer = new LampagerTransformer($builder, $options);
    }

    /**
     * @return static
     */
    public static function create(Model $builder, array $options)
    {
        return new static($builder, $options);
    }

    /**
     * Add cursor parameter name for ORDER BY statement.
     *
     * @param  int|string $column
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

        // Merge with existing options
        $this->options = array_replace_recursive($this->options ?: [], $options);

        if (isset($options['order'])) {
            foreach ($options['order'] as $column => $order) {
                $this->orderBy($column, $order);
            }
        }

        return parent::fromArray($options);
    }

    /**
     * Transform Query to CakePHP query.
     *
     * @param  Query $query Query.
     * @return array Options for Model::find.
     */
    public function transform(Query $query)
    {
        return $this->transformer->transform($query);
    }

    /**
     * Build query from the cursor.
     *
     * @param  int[]|string[] $cursor Cursor.
     * @return array          Options for Model::find.
     */
    public function build(array $cursor = [])
    {
        return $this->transform($this->configure(new LampagerArrayCursor($this->builder, $cursor)));
    }

    /**
     * @param  int[]|string[]   $cursor Cursor.
     * @return PaginationResult Result.
     */
    public function paginate(array $cursor = [])
    {
        $query = $this->configure(new LampagerArrayCursor($this->builder, $cursor));
        return $this->processor->process($query, $this->builder->find('all', $this->transform($query)));
    }
}

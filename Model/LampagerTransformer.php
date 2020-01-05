<?php

App::uses('LampagerPaginator', 'Lampager.Model');
App::uses('Sqlite', 'Model/Datasource/Database');

use Lampager\Query;
use Lampager\Query\ConditionGroup;
use Lampager\Query\Select;
use Lampager\Query\SelectOrUnionAll;
use Lampager\Query\UnionAll;

class LampagerTransformer
{
    /** @var Model */
    protected $builder;

    /** @var array */
    protected $options;

    public function __construct(Model $builder, array $options)
    {
        $this->builder = $builder;
        $this->options = $options;
    }

    /**
     * Transform Query to CakePHP query.
     *
     * @return array Options for Model::find.
     */
    public function transform(Query $query)
    {
        return [
            // Compiled by static::compileSelect
            'conditions' => null,

            // Compiled by static::compileLimit
            'limit' => null,

            // Sort the result set
            'order' => $this->compileOrderBy($query->selectOrUnionAll()),

            // Used along with ArrayProcessor
            'config' => $query,

            // Create subquery and inner join with it
            'joins' => array_merge(
                [
                    [
                        'type' => 'INNER',
                        'table' => $this->compileSelectOrUnionAll($query->selectOrUnionAll()),
                        'alias' => LampagerPaginator::class,
                        'conditions' => [
                            LampagerPaginator::class . ".{$this->builder->primaryKey} = {$this->builder->alias}.{$this->builder->primaryKey}",
                        ],
                    ],
                ],
                isset($this->options['joins']) ? $this->options['joins'] : []
            ),
        ] + $this->options;
    }

    /**
     * @param  Select|UnionAll $selectOrUnionAll
     * @return string
     */
    protected function compileSelectOrUnionAll(SelectOrUnionAll $selectOrUnionAll)
    {
        if ($selectOrUnionAll instanceof Select) {
            return '(' . $this->compileSelect($selectOrUnionAll) . ')';
        }

        if ($selectOrUnionAll instanceof UnionAll) {
            $supportQuery = $this->compileSelect($selectOrUnionAll->supportQuery());
            $mainQuery = $this->compileSelect($selectOrUnionAll->mainQuery());

            if ($this->builder->getDataSource() instanceof Sqlite) {
                return '(SELECT * FROM (' . $supportQuery . ') UNION ALL SELECT * FROM (' . $mainQuery . '))';
            }

            return '((' . $supportQuery . ') UNION ALL (' . $mainQuery . '))';
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('Unreachable here');
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return string
     */
    protected function compileSelect(Select $select)
    {
        /** @var DboSource $db */
        $db = $this->builder->getDataSource();

        return $db->buildStatement([
            'limit' => $this->compileLimit($select),
            'order' => $this->compileOrderBy($select),
            'conditions' => array_merge_recursive(
                $this->compileWhere($select),
                isset($this->options['conditions']) ? $this->options['conditions'] : []
            ),
            'alias' => $this->builder->alias,
            'table' => $db->fullTableName($this->builder),
            'fields' => [
                $db->name("{$this->builder->alias}.{$this->builder->primaryKey}"),
            ],
        ], $this->builder);
    }

    /**
     * @return string[]
     */
    protected function compileWhere(Select $select)
    {
        $conditions = [];
        foreach ($select->where() as $group) {
            $conditions['OR'][] = iterator_to_array($this->compileWhereGroup($group));
        }
        return $conditions;
    }

    /**
     * @return \Generator<string,string>
     */
    protected function compileWhereGroup(ConditionGroup $group)
    {
        foreach ($group as $condition) {
            $column = $condition->left() . ' ' . $condition->comparator();
            $value = $condition->right();
            yield $column => $value;
        }
    }

    /**
     * @param  Select|UnionAll $selectOrUnionAll
     * @return string[]
     */
    protected function compileOrderBy(SelectOrUnionAll $selectOrUnionAll)
    {
        if ($selectOrUnionAll instanceof Select) {
            $select = $selectOrUnionAll;
        }
        if ($selectOrUnionAll instanceof UnionAll) {
            $select = $selectOrUnionAll->mainQuery();
        }

        $orders = [];
        foreach ($select->orders() as $order) {
            $orders[$order->column()] = $order->order();
        }
        return $orders;
    }

    /**
     * @return int
     */
    protected function compileLimit(Select $select)
    {
        return $select->limit()->toInteger();
    }
}

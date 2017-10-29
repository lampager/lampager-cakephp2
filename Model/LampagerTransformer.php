<?php

// @codeCoverageIgnoreStart
App::uses('LampagerArrayCursor', 'Lampager.Model');
App::uses('LampagerPaginator', 'Lampager.Model');
// @codeCoverageIgnoreEnd

use Lampager\Cursor;
use Lampager\Query;
use Lampager\Query\Select;
use Lampager\Query\SelectOrUnionAll;
use Lampager\Query\UnionAll;
use Lampager\Query\ConditionGroup;

class LampagerTransformer
{
    /** @var LampagerPaginator */
    protected $paginator;

    public function __construct(LampagerPaginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Transform Query to CakePHP query.
     *
     * @param  Query $query Query.
     * @return array        Options for Model::find.
     */
    public function transform(Query $query)
    {
        $model = $this->paginator->builder;
        return [
            // Compiled by static::compileSelect
            'conditions' => null,

            // Compiled by static::compileLimit
            'limit' => null,

            // Compiled by static::compileOrderBy
            'order' => null,

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
                            LampagerPaginator::class . ".{$model->primaryKey} = {$model->alias}.{$model->primaryKey}",
                        ],
                    ],
                ],
                $this->paginator->query['joins'] ?: []
            ),
        ] + $this->paginator->query;
    }

    /**
     * Build query from the cursor.
     *
     * @param  Cursor|int[]|string[] $cursor Cursor.
     * @return array                         Options for Model::find.
     */
    public function build($cursor = [])
    {
        return $this->transform($this->paginator->configure(new LampagerArrayCursor($this->paginator->builder, $cursor)));
    }

    /**
     * @param  SelectOrUnionAll $select
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
            return '(SELECT * FROM (' . $supportQuery . ') UNION ALL SELECT * FROM (' . $mainQuery . '))';
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('Unreachable here');
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param  Select $select
     * @return string
     */
    protected function compileSelect(Select $select)
    {
        $model = $this->paginator->builder;
        $query = $this->paginator->query;

        /** @var DboSource */
        $db = $model->getDataSource();

        return $db->buildStatement([
            'limit' => $this->compileLimit($select),
            'order' => $this->compileOrderBy($select),
            'conditions' => array_merge_recursive(
                $this->compileWhere($select),
                $query['conditions'] ?: []
            ),
            'alias' => $model->alias,
            'table' => $db->fullTableName($model),
            'fields' => [
                "{$model->alias}.{$model->primaryKey}",
            ],
        ], $model);
    }

    /**
     * @param  Select   $select
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
     * @param  ConditionGroup     $group
     * @return \Generator<string, string>
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
     * @param  Select   $select
     * @return string[]
     */
    protected function compileOrderBy(Select $select)
    {
        $orders = [];
        foreach ($select->orders() as $order) {
            $orders[$order->column()] = $order->order();
        }
        return $orders;
    }

    /**
     * @param  Select $select
     * @return int
     */
    protected function compileLimit(Select $select)
    {
        return $select->limit()->toInteger();
    }
}

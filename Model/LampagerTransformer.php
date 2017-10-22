<?php
use Lampager\Query\Query;
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

    public function transform(Query $query)
    {
        return $this->compileSelectOrUnionAll($query->selectOrUnionAll());
    }

    public function build(array $cursor = [])
    {
        return $this->transform($this->paginator->configure($cursor));
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
            return '((' . $supportQuery . ') UNION ALL (' . $mainQuery . '))';
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
            'offset' => null,
            'group' => null,
            'joins' => [],
        ], $model);
    }

    /**
     * @param  Select $select
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
     * @param  ConditionGroup $group
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
     * @param  Select $select
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

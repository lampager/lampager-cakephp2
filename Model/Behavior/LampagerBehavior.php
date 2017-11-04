<?php

// @codeCoverageIgnoreStart
App::uses('ModelBehavior', 'Model');
App::uses('LampagerPaginator', 'Lampager.Model');
App::uses('LampagerArrayProcessor', 'Lampager.Model');
// @codeCoverageIgnoreEnd

class LampagerBehavior extends ModelBehavior
{
    /** @var string[] */
    public $mapMethods = [
        '/\b_findLampager\b/' => 'findLampager',
    ];

    /**
     * {@inheritdoc}
     */
    public function setup(Model $model, $config = [])
    {
        $model->findMethods['lampager'] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup(Model $model)
    {
        unset($model->findMethods['lampager']);
    }

    /**
     * Hanldle the custom finder. Only called by Model::find().
     *
     * @param  Model  $model   Model.
     * @param  string $method  Method.
     * @param  string $state   Either "before" or "after"
     * @param  array  $query   Query.
     * @param  array  $results Results.
     * @return array
     */
    public function findLampager(Model $model, $method, $state, array $query = [], array $results = [])
    {
        return $this->{__FUNCTION__ . ucfirst($state)}($model, $query, $results);
    }

    protected function findLampagerBefore(Model $model, array $query = [], array $results = [])
    {
        return LampagerPaginator::create($model, $query)->transformer->build(isset($query['cursor']) ? $query['cursor'] : []);
    }

    protected function findLampagerAfter(Model $model, array $query = [], array $results = [])
    {
        return LampagerArrayProcessor::create($model)->process($query['config'], $results);
    }

    /**
     * Paginate the Model. Only called by PaginatorComponent::paginate().
     *
     * @param Model $model
     * @param array $conditions
     * @param array $fields
     * @param array $order
     * @param int   $limit
     * @param int   $page
     * @param int   $recursive
     * @param array $extra
     */
    public function paginate(Model $model, $conditions, $fields, $order, $limit, $page = 1, $recursive = null, array $extra = [])
    {
        /**
         * Extract extra parameters which may include
         *
         * @var bool  $forward
         * @var bool  $backward
         * @var bool  $exclusive
         * @var bool  $inclusive
         * @var bool  $seekable
         * @var bool  $unseekable
         * @var array $cursor
         */
        extract($extra, EXTR_SKIP);

        return $model->find('lampager', compact(
            'conditions',
            'fields',
            'order',
            'limit',
            'page',
            'recursive',
            'forward',
            'backward',
            'exclusive',
            'inclusive',
            'seekable',
            'unseekable',
            'cursor'
        ));
    }
}

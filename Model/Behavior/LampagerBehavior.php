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
}

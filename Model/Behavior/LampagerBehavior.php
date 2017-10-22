<?php

App::uses('ModelBehavior', 'Model');
App::uses('LampagerPaginator', 'Lampager.Model');
App::uses('LampagerArrayCursor', 'Lampager.Model');
App::uses('LampagerArrayProcessor', 'Lampager.Model');

use Lampager\Concerns\HasProcessor;

class LampagerBehavior extends ModelBehavior
{
    use HasProcessor;

    /** @var LampagerArrayProcessor[] */
    protected $processors;

    public $mapMethods = [
        '/\b_findLampager\b/' => 'findLampager',
    ];

    /**
     * {@inheritdoc}
     */
    public function setup(Model $model, $config = [])
    {
        $this->processors[$model->alias] = new LampagerArrayProcessor($model);
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
        $cursor = isset($query['cursor']) ? $query['cursor'] : [];
        $lampager = LampagerPaginator::fromQuery($model, $query);
        $config = $lampager->configure(new LampagerArrayCursor($cursor, $model));

        return [
            'joins' => array_merge(
                [
                    [
                        'type' => 'INNER',
                        'table' => $lampager->transformer->transform($config),
                        'alias' => LampagerPaginator::class,
                        'conditions' => [
                            LampagerPaginator::class . ".{$model->primaryKey} = {$model->alias}.{$model->primaryKey}",
                        ],
                    ],
                ],
                $query['joins'] ?: []
            ),
            'config' => $config,
            'callbacks' => $query['callbacks'],
            'fields' => $query['fields'],
            'conditions' => null,
            'group' => null,
            'limit' => null,
            'offset' => null,
            'order' => null,
            'page' => null,
        ];
    }

    protected function findLampagerAfter(Model $model, array $query = [], array $results = [])
    {
        return $this->processors[$model->alias]->process($query['config'], $results);
    }
}

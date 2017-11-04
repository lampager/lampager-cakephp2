<?php

App::uses('CakeRequest', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('Controller', 'Controller');
App::uses('PaginatorComponent', 'Controller/Component');
App::uses('LampagerTestCase', 'Test/Case');
App::uses('LampagerArrayCursor', 'Model');
App::uses('LampagerArrayProcessor', 'Model');

use Lampager\Query\Order;

class LampagerArrayProcessorTest extends LampagerTestCase
{
    /** @var Model */
    protected $Post;

    /** @var CakeRequest */
    protected $request;

    /** @var Controller */
    protected $Controller;

    /** @var PaginatorComponent */
    protected $Paginator;

    /** @var string[] */
    public $fixtures = [
        'app.Post',
    ];

    public function setUp()
    {
        parent::setUp();

        // Prepare for ModelBehavior
        $this->Post = ClassRegistry::init('Post');
        $this->Post->Behaviors->load('Lampager');

        // Prepare for PaginatorComponent
        $this->request = new CakeRequest('posts/index');
        $this->request->params['pass'] = [];
        $this->request->params['named'] = [];

        $this->Controller = new Controller($this->request);
        $this->Controller->Post = $this->Post;

        $this->Paginator = new PaginatorComponent($this->getMock(ComponentCollection::class), []);
        $this->Paginator->Controller = $this->Controller;
    }

    public function tearDown()
    {
        // Shutdown for ModelBehavior
        $this->Post->Behaviors->unload('Lampager');

        // Shutdown for PaginatorComponent
        $this->Controller->Components->unload('Paginator');

        parent::tearDown();
    }

    /**
     * Test LampagerArrayProcessor::process by custom finder
     *
     * @param array $query
     * @param mixed $expected
     * @dataProvider processProvider
     */
    public function testProcessByFinder(array $query, $expected)
    {
        $this->assertSame($expected, $this->Post->find('lampager', $query));
    }

    /**
     * Test LampagerArrayProcessor::process by PaginatorComponent
     *
     * @param array $query
     * @param mixed $expected
     * @dataProvider processProvider
     */
    public function testProcessByComponent(array $query, $expected)
    {
        $this->Paginator->settings = $query;
        $this->assertSame($expected, $this->Paginator->paginate('Post'));
    }

    public function processProvider()
    {
        yield 'Ascending forward start inclusive' => [
            [
                'forward' => true,
                'seekable' => true,
                'limit' => 3,
                'order' => [
                    'Post.modified' => 'ASC',
                    'Post.id' => 'ASC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'next_cursor' => [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Ascending forward start exclusive' => [
            [
                'forward' => true,
                'seekable' => true,
                'exclusive' => true,
                'limit' => 3,
                'order' => [
                    'Post.modified' => 'ASC',
                    'Post.id' => 'ASC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'next_cursor' => [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Ascending forward inclusive' => [
            [
                'forward' => true,
                'seekable' => true,
                'limit' => 3,
                'cursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'order' => [
                    'Post.modified' => 'ASC',
                    'Post.id' => 'ASC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    'next_cursor' => [
                        'Post' => [
                            'id' => '4',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Ascending forward exclusive' => [
            [
                'forward' => true,
                'seekable' => true,
                'exclusive' => true,
                'limit' => 3,
                'cursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'order' => [
                    'Post.modified' => 'ASC',
                    'Post.id' => 'ASC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '4',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    'next_cursor' => null,
                ],
            ],
        ];

        yield 'Ascending backward start inclusive' => [
            [
                'backward' => true,
                'seekable' => true,
                'limit' => 3,
                'order' => [
                    'Post.modified' => 'ASC',
                    'Post.id' => 'ASC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '4',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Ascending backward start exclusive' => [
            [
                'backward' => true,
                'seekable' => true,
                'exclusive' => true,
                'limit' => 3,
                'order' => [
                    'Post.modified' => 'ASC',
                    'Post.id' => 'ASC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '4',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Ascending backward inclusive' => [
            [
                'backward' => true,
                'seekable' => true,
                'limit' => 3,
                'cursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'order' => [
                    'Post.modified' => 'ASC',
                    'Post.id' => 'ASC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => null,
                    'next_cursor' => [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Ascending backward exclusive' => [
            [
                'backward' => true,
                'seekable' => true,
                'exclusive' => true,
                'limit' => 3,
                'cursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'order' => [
                    'Post.modified' => 'ASC',
                    'Post.id' => 'ASC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => null,
                    'next_cursor' => [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Descending forward start inclusive' => [
            [
                'forward' => true,
                'seekable' => true,
                'limit' => 3,
                'order' => [
                    'Post.modified' => 'DESC',
                    'Post.id' => 'DESC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '4',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'next_cursor' => [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Descending forward start exclusive' => [
            [
                'forward' => true,
                'seekable' => true,
                'exclusive' => true,
                'limit' => 3,
                'order' => [
                    'Post.modified' => 'DESC',
                    'Post.id' => 'DESC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '4',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'next_cursor' => [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Descending forward inclusive' => [
            [
                'forward' => true,
                'seekable' => true,
                'limit' => 3,
                'cursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'order' => [
                    'Post.modified' => 'DESC',
                    'Post.id' => 'DESC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    'next_cursor' => null,
                ],
            ],
        ];

        yield 'Descending forward exclusive' => [
            [
                'forward' => true,
                'seekable' => true,
                'exclusive' => true,
                'limit' => 3,
                'cursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'order' => [
                    'Post.modified' => 'DESC',
                    'Post.id' => 'DESC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    'next_cursor' => null,
                ],
            ],
        ];

        yield 'Descending backward start inclusive' => [
            [
                'backward' => true,
                'seekable' => true,
                'limit' => 3,
                'order' => [
                    'Post.modified' => 'DESC',
                    'Post.id' => 'DESC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Descending backward start exclusive' => [
            [
                'backward' => true,
                'seekable' => true,
                'exclusive' => true,
                'limit' => 3,
                'order' => [
                    'Post.modified' => 'DESC',
                    'Post.id' => 'DESC',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Descending backward inclusive' => [
            [
                'backward' => true,
                'seekable' => true,
                'limit' => 3,
                'cursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'order' => [
                    'Post.modified' => 'desc',
                    'Post.id' => 'desc',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '3',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => [
                        'Post' => [
                            'id' => '4',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    'next_cursor' => [
                        'Post' => [
                            'id' => '1',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];

        yield 'Descending backward exlusive' => [
            [
                'backward' => true,
                'seekable' => true,
                'exclusive' => true,
                'limit' => 3,
                'cursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'order' => [
                    'Post.modified' => 'desc',
                    'Post.id' => 'desc',
                ],
            ],
            [
                'records' => [
                    [
                        'Post' => [
                            'id' => '4',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '2',
                            'modified' => '2017-01-01 11:00:00',
                        ],
                    ],
                    [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
                'meta' => [
                    'previous_cursor' => null,
                    'next_cursor' => [
                        'Post' => [
                            'id' => '5',
                            'modified' => '2017-01-01 10:00:00',
                        ],
                    ],
                ],
            ],
        ];
    }
}

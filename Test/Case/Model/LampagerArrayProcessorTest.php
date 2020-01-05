<?php

App::uses('CakeRequest', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('Controller', 'Controller');
App::uses('PaginatorComponent', 'Controller/Component');
App::uses('LampagerTestCase', 'Lampager.Test/Case');
App::uses('LampagerArrayCursor', 'Lampager.Model');
App::uses('LampagerArrayProcessor', 'Lampager.Model');

use Lampager\PaginationResult;

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
        'plugin.Lampager.Post',
    ];

    public function setUp()
    {
        parent::setUp();

        /** @var ComponentCollection&PHPUnit_Framework_MockObject_MockObject */
        $collection = $this->createMock(ComponentCollection::class);

        // Prepare for ModelBehavior
        $this->Post = ClassRegistry::init('Post');
        $this->Post->Behaviors->load('Lampager.Lampager');

        // Prepare for PaginatorComponent
        $this->request = new CakeRequest('posts/index');
        $this->request->params['pass'] = [];
        $this->request->params['named'] = [];

        $this->Controller = new Controller($this->request);
        $this->Controller->Post = $this->Post;

        $this->Paginator = new PaginatorComponent($collection, []);
        $this->Paginator->Controller = $this->Controller;
    }

    public function tearDown()
    {
        // Shutdown for ModelBehavior
        $this->Post->Behaviors->unload('Lampager.Lampager');

        // Shutdown for PaginatorComponent
        $this->Controller->Components->unload('Paginator');

        parent::tearDown();
    }

    /**
     * Test LampagerArrayProcessor::process by custom finder
     *
     * @param mixed $expected
     * @dataProvider processProvider
     */
    public function testProcessByFinder(array $query, $expected)
    {
        $actual = $this->Post->find('lampager', $query);
        $this->assertInstanceOf(PaginationResult::class, $actual);
        $this->assertSame($expected, (array)$actual);
    }

    /**
     * Test LampagerArrayProcessor::process by PaginatorComponent
     *
     * @param mixed $expected
     * @dataProvider processProvider
     */
    public function testProcessByComponent(array $query, $expected)
    {
        $this->Paginator->settings = $query;
        $actual = $this->Paginator->paginate('Post');
        $this->assertInstanceOf(PaginationResult::class, $actual);
        $this->assertSame($expected, (array)$actual);
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
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Post' => [
                        'id' => '2',
                        'modified' => '2017-01-01 11:00:00',
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
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Post' => [
                        'id' => '5',
                        'modified' => '2017-01-01 10:00:00',
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
                'hasPrevious' => true,
                'previousCursor' => [
                    'Post' => [
                        'id' => '1',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'hasNext' => true,
                'nextCursor' => [
                    'Post' => [
                        'id' => '4',
                        'modified' => '2017-01-01 11:00:00',
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
                'hasPrevious' => true,
                'previousCursor' => [
                    'Post' => [
                        'id' => '5',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'hasNext' => false,
                'nextCursor' => null,
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
                'hasPrevious' => true,
                'previousCursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'hasNext' => null,
                'nextCursor' => null,
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
                'hasPrevious' => true,
                'previousCursor' => [
                    'Post' => [
                        'id' => '5',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'hasNext' => null,
                'nextCursor' => null,
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
                'hasPrevious' => false,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Post' => [
                        'id' => '5',
                        'modified' => '2017-01-01 10:00:00',
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
                'hasPrevious' => false,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Post' => [
                        'id' => '1',
                        'modified' => '2017-01-01 10:00:00',
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
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Post' => [
                        'id' => '3',
                        'modified' => '2017-01-01 10:00:00',
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
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Post' => [
                        'id' => '5',
                        'modified' => '2017-01-01 10:00:00',
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
                'hasPrevious' => true,
                'previousCursor' => [
                    'Post' => [
                        'id' => '5',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'hasNext' => false,
                'nextCursor' => null,
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
                'hasPrevious' => true,
                'previousCursor' => [
                    'Post' => [
                        'id' => '1',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'hasNext' => false,
                'nextCursor' => null,
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
                'hasPrevious' => true,
                'previousCursor' => [
                    'Post' => [
                        'id' => '2',
                        'modified' => '2017-01-01 11:00:00',
                    ],
                ],
                'hasNext' => null,
                'nextCursor' => null,
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
                'hasPrevious' => true,
                'previousCursor' => [
                    'Post' => [
                        'id' => '5',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
                'hasNext' => null,
                'nextCursor' => null,
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
                'hasPrevious' => true,
                'previousCursor' => [
                    'Post' => [
                        'id' => '4',
                        'modified' => '2017-01-01 11:00:00',
                    ],
                ],
                'hasNext' => true,
                'nextCursor' => [
                    'Post' => [
                        'id' => '1',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
            ],
        ];

        yield 'Descending backward exclusive' => [
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
                'hasPrevious' => false,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'Post' => [
                        'id' => '5',
                        'modified' => '2017-01-01 10:00:00',
                    ],
                ],
            ],
        ];
    }
}

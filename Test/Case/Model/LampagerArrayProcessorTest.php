<?php

App::uses('LampagerTestCase', 'Test/Case');
App::uses('LampagerArrayCursor', 'Model');
App::uses('LampagerArrayProcessor', 'Model');

use Lampager\Query\Order;

class LampagerArrayProcessorTest extends LampagerTestCase
{
    /** @var Model */
    protected $Post;

    /** @var string[] */
    public $fixtures = [
        'app.Post',
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Post = ClassRegistry::init('Post');
        $this->Post->Behaviors->load('Lampager');
    }

    public function tearDown()
    {
        $this->Post->Behaviors->unload('Lampager');
        parent::tearDown();
    }

    /**
     * Test LampagerArrayProcessor::process
     *
     * @param array $query
     * @param mixed $expected
     * @dataProvider processProvider
     */
    public function testProcess(array $query, $expected)
    {
        $this->assertSame($expected, $this->Post->find('lampager', $query));
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

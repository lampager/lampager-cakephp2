<?php

App::uses('LampagerTestCase', 'Lampager.Test/Case');
App::uses('LampagerPaginator', 'Lampager.Model');

class LampagerPaginatorTest extends LampagerTestCase
{
    /** @var Model */
    protected $Post;

    /** @var string[] */
    public $fixtures = [
        'plugin.Lampager.Post',
    ];

    public function setUp()
    {
        parent::setUp();
        $this->Post = ClassRegistry::init('Post');
    }

    /**
     * Test LampagerPaginator::order
     *
     * @param array $query
     * @param array $expected
     * @dataProvider queryProvider
     */
    public function testFromQuery(array $query, array $expected)
    {
        $expected = (object)$expected;
        $paginator = LampagerPaginator::create($this->Post, $query);
        $this->assertSame($expected->orders, $paginator->orders);
        $this->assertSame($expected->limit, $paginator->limit);
        $this->assertSame($expected->backward, $paginator->backward);
        $this->assertSame($expected->exclusive, $paginator->exclusive);
        $this->assertSame($expected->seekable, $paginator->seekable);
    }

    public function queryProvider()
    {
        yield 'Order by Model.column => order' => [
            [
                'order' => [
                    'Post.id' => 'ASC',
                    'Post.modified' => 'DESC',
                ],
                'limit' => 15,
                'backward' => true,
                'exclusive' => true,
                'seekable' => true,
            ],
            [
                'orders' => [
                    ['Post.id', 'asc'],
                    ['Post.modified', 'desc'],
                ],
                'limit' => 15,
                'backward' => true,
                'exclusive' => true,
                'seekable' => true,
            ]
        ];

        yield 'Order by Model.column order' => [
            [
                'order' => [
                    'Post.id ASC',
                    'Post.modified DESC',
                ],
                'limit' => 15,
                'backward' => true,
                'exclusive' => true,
                'seekable' => true,
            ],
            [
                'orders' => [
                    ['Post.id', 'asc'],
                    ['Post.modified', 'desc'],
                ],
                'limit' => 15,
                'backward' => true,
                'exclusive' => true,
                'seekable' => true,
            ]
        ];

        yield 'Order by column => order' => [
            [
                'order' => [
                    'id' => 'ASC',
                    'modified' => 'DESC',
                ],
                'limit' => 15,
                'backward' => true,
                'exclusive' => true,
                'seekable' => true,
            ],
            [
                'orders' => [
                    ['Post.id', 'asc'],
                    ['Post.modified', 'desc'],
                ],
                'limit' => 15,
                'backward' => true,
                'exclusive' => true,
                'seekable' => true,
            ]
        ];

        yield 'Order by column order' => [
            [
                'order' => [
                    'id ASC',
                    'modified DESC',
                ],
                'limit' => 15,
                'backward' => true,
                'exclusive' => true,
                'seekable' => true,
            ],
            [
                'orders' => [
                    ['Post.id', 'asc'],
                    ['Post.modified', 'desc'],
                ],
                'limit' => 15,
                'backward' => true,
                'exclusive' => true,
                'seekable' => true,
            ]
        ];
    }
}

<?php

App::uses('LampagerTestCase', 'Lampager.Test/Case');
App::uses('LampagerColumnAccess', 'Lampager.Model');

class LampagerColumnAccessTest extends LampagerTestCase
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
     * Test LampagerColumnAccess::get
     *
     * @param array  $data
     * @param string $column
     * @param mixed  $expected
     * @dataProvider getProvider
     */
    public function testGet(array $data, $column, $expected)
    {
        $access = new LampagerColumnAccess($this->Post);
        $this->assertSame($expected, $access->get($data, $column));
    }

    /**
     * Test LampagerColumnAccess::has
     *
     * @param array  $data
     * @param string $column
     * @param mixed  $expected
     * @dataProvider hasProvider
     */
    public function testHas(array $data, $column, $expected)
    {
        $access = new LampagerColumnAccess($this->Post);
        $this->assertSame($expected, $access->has($data, $column));
    }

    /**
     * Test LampagerColumnAccess::with
     *
     * @param string $column
     * @param mixed  $value
     * @param mixed  $expected
     * @dataProvider withProvider
     */
    public function testWith($column, $value, $expected)
    {
        $access = new LampagerColumnAccess($this->Post);
        $this->assertSame($expected, $access->with($column, $value));
    }

    /**
     * Test LampagerColumnAccess::with
     *
     * @param array $data
     * @param mixed $expected
     * @dataProvider iterateProvider
     */
    public function testIterate(array $data, $expected)
    {
        $access = new LampagerColumnAccess($this->Post);
        $this->assertSame($expected, iterator_to_array($access->iterate($data)));
    }

    public function getProvider()
    {
        yield 'Multi-dimensional Model array from Model.column' => [
            [
                'Post' => [
                    'id' => 1,
                ],
            ],
            'Post.id',
            1,
        ];

        yield 'Multi-dimensional Model array from column' => [
            [
                'Post' => [
                    'id' => 1,
                ],
            ],
            'id',
            1,
        ];

        yield 'Single-dimensional Model.column array from Model.column' => [
            [
                'Post.id' => 1,
            ],
            'Post.id',
            1,
        ];

        yield 'Single-dimensional Model.column array from column' => [
            [
                'Post.id' => 1,
            ],
            'id',
            1,
        ];

        yield 'Single-dimensional column array from column' => [
            [
                'id' => 1,
            ],
            'id',
            1,
        ];

        yield 'Multi-dimensional Model array from non-existent column' => [
            [
                'Post' => [
                    'id' => 1,
                ],
            ],
            'modified',
            null,
        ];

        yield 'Single-dimensional Model.column array from non-existent Model.column' => [
            [
                'Post.id' => 1,
            ],
            'Post.modified',
            null,
        ];

        yield 'Empty array' => [
            [],
            'Post.id',
            null,
        ];
    }

    public function hasProvider()
    {
        yield 'Multi-dimensional Model array from Model.column' => [
            [
                'Post' => [
                    'id' => 1,
                ],
            ],
            'Post.id',
            true,
        ];

        yield 'Multi-dimensional Model array from column' => [
            [
                'Post' => [
                    'id' => 1,
                ],
            ],
            'id',
            true,
        ];

        yield 'Single-dimensional Model.column array from Model.column' => [
            [
                'Post.id' => 1,
            ],
            'Post.id',
            true,
        ];

        yield 'Single-dimensional Model.column array from column' => [
            [
                'Post.id' => 1,
            ],
            'id',
            true,
        ];

        yield 'Single-dimensional column array from column' => [
            [
                'id' => 1,
            ],
            'id',
            true,
        ];

        yield 'Multi-dimensional Model array from non-existent column' => [
            [
                'Post' => [
                    'id' => 1,
                ],
            ],
            'modified',
            false,
        ];

        yield 'Single-dimensional Model.column array from non-existent Model.column' => [
            [
                'Post.id' => 1,
            ],
            'Post.modified',
            false,
        ];

        yield 'Empty array' => [
            [],
            'Post.id',
            false,
        ];
    }

    public function withProvider()
    {
        yield 'Model.column to multi-dimensional Model array' => [
            'Post.id',
            1,
            [
                'Post' => [
                    'id' => 1,
                ],
            ],
        ];

        yield 'Column to multi-dimensional Model array' => [
            'id',
            1,
            [
                'Post' => [
                    'id' => 1,
                ],
            ],
        ];
    }

    public function iterateProvider()
    {
        yield 'Single-dimensional Model.column array' => [
            [
                'Post.id' => '1',
                'Post.modified' => '2017-01-01 10:00:00',
            ],
            [
                'Post.id' => '1',
                'Post.modified' => '2017-01-01 10:00:00',
            ],
        ];

        yield 'Multi-dimensional Model.column array' => [
            [
                'Post' => [
                    'id' => '1',
                    'modified' => '2017-01-01 10:00:00',
                ],
            ],
            [
                'Post.id' => '1',
                'Post.modified' => '2017-01-01 10:00:00',
            ],
        ];
    }
}

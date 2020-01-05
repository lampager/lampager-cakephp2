<?php

App::uses('LampagerArrayCursor', 'Lampager.Model');
App::uses('LampagerTestCase', 'Lampager.Test/Case');

class LampagerArrayCursorTest extends LampagerTestCase
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
     * Test LampagerArrayCursor::get
     *
     * @param string $column
     * @param mixed  $expected
     * @dataProvider getProvider
     */
    public function testGet(array $data, $column, $expected)
    {
        $access = new LampagerArrayCursor($this->Post, $data);
        $this->assertSame($expected, $access->get($column));
    }

    /**
     * Test LampagerArrayCursor::has
     *
     * @param string $column
     * @param mixed  $expected
     * @dataProvider hasProvider
     */
    public function testHas(array $data, $column, $expected)
    {
        $access = new LampagerArrayCursor($this->Post, $data);
        $this->assertSame($expected, $access->has($column));
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
            null,
        ];
    }
}

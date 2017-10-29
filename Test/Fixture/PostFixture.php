<?php

App::uses('CakeTestFixture', 'TestSuite/Fixture');

class PostFixture extends CakeTestFixture
{
    public $fields = [
        'id' => [
            'type' => 'integer',
            'key' => 'primary',
        ],
        'modified' => 'datetime',
    ];

    public $records = [
        ['id' => 1, 'modified' => '2017-01-01 10:00:00'],
        ['id' => 3, 'modified' => '2017-01-01 10:00:00'],
        ['id' => 5, 'modified' => '2017-01-01 10:00:00'],
        ['id' => 2, 'modified' => '2017-01-01 11:00:00'],
        ['id' => 4, 'modified' => '2017-01-01 11:00:00'],
    ];
}

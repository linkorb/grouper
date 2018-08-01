<?php

namespace Grouper\Test;

use PHPUnit\Framework\TestCase;

use Grouper\Grouper;

class GrouperTest extends TestCase
{
    private $grouper;

    protected function setUp()
    {
        $this->grouper = new Grouper();
    }

    public function testGroupWillReturnEmptyArrayWhenRowsArgIsEmpty()
    {
        $this->assertCount(0, $this->grouper->group([], []));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The columns argument cannot be an empty list
     */
    public function testGroupWillThrowInvalidArgExceptionWhenColumnsArgIsEmpty()
    {
        $this->grouper->group(
            [
                ['/row']
            ],
            []
        );
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage All rows must be an associative array
     */
    public function testGroupWillThrowUnxepectedValueExceptionWhenAnyRowIsNotAnArray()
    {
        $this->grouper->group(
            [
                ['id' => '/row'],
                '/not-a-row'
            ],
            ['name'],
            'id'
        );
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage All rows must have an entry in the "id" column
     */
    public function testGroupWillThrowUnxepectedValueExceptionWhenAllRowsExhibitTheKeyColumn()
    {
        $this->grouper->group(
            [
                ['name' => 'alice']
            ],
            ['name'],
            'id'
        );
    }

    /**
     * @dataProvider groupData
     */
    public function testGroupWillGroup($columns, $id, $input, $output)
    {
        $this->assertSame(
            $output,
            $this->grouper->group($input, $columns, $id)
        );
    }

    /**
     * @dataProvider groupData
     */
    public function groupData()
    {
        return [
            'Single column comparison' => [
                ['name'],
                'id',
                [
                    [
                        'id' => 1,
                        'name' => 'Alice',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Alice',
                    ],
                ],
                [
                    [
                        [
                            'id' => 1,
                            'name' => 'Alice',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Alice',
                        ],
                    ]
                ]
            ],
            'Two column comparison' => [
                ['name', 'addr'],
                'id',
                [
                    [
                        'id' => 1,
                        'name' => 'Alice',
                        'addr' => '1.2.3',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Alice',
                        'addr' => '4.5.6',
                    ],
                    [
                        'id' => 3,
                        'name' => 'Bob',
                        'addr' => '1.2.3',
                    ],
                ],
                [
                    [
                        [
                            'id' => 1,
                            'name' => 'Alice',
                            'addr' => '1.2.3',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Alice',
                            'addr' => '4.5.6',
                        ],
                        [
                            'id' => 3,
                            'name' => 'Bob',
                            'addr' => '1.2.3',
                        ],
                    ]
                ]
            ],
            'Three column comparison' => [
                ['name', 'addr', 'code'],
                'id',
                [
                    [
                        'id' => 1,
                        'name' => 'Alice',
                        'addr' => '1.2.3',
                        'code' => 'X00',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Alice',
                        'addr' => '4.5.6',
                        'code' => 'X01',
                    ],
                    [
                        'id' => 3,
                        'name' => 'Bob',
                        'addr' => '1.2.3',
                        'code' => 'X0X',
                    ],
                    [
                        'id' => 4,
                        'name' => 'Claire',
                        'addr' => '7.8.9',
                        'code' => 'X0X',
                    ],
                ],
                [
                    [
                        [
                            'id' => 1,
                            'name' => 'Alice',
                            'addr' => '1.2.3',
                            'code' => 'X00',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Alice',
                            'addr' => '4.5.6',
                            'code' => 'X01',
                        ],
                        [
                            'id' => 3,
                            'name' => 'Bob',
                            'addr' => '1.2.3',
                            'code' => 'X0X',
                        ],
                        [
                            'id' => 4,
                            'name' => 'Claire',
                            'addr' => '7.8.9',
                            'code' => 'X0X',
                        ],
                    ]
                ]
            ],
            'Taken from the examples' => [
                ['email', 'phone'],
                'id',
                [ // input
                    [
                        'id' => 1,
                        'name' => 'Alice',
                        'email' => 'alice@hotmail.com',
                        'phone' => '9999999999',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Alice',
                        'email' => 'alice@outlook.com',
                        'phone' => '9999999999',
                    ],
                    [
                        'id' => 3,
                        'name' => 'Bob',
                        'email' => 'bob@outlook.com',
                        'phone' => '88888888888',
                    ],
                    [
                        'id' => 4,
                        'name' => 'Alice',
                        'email' => 'alice@outlook.com',
                        'phone' => '11111111111',
                    ],
                    [
                        'id' => 5,
                        'name' => 'Claire',
                        'email' => 'claire@gmail.com',
                        'phone' => '777777777777',
                    ],
                    [
                        'id' => 6,
                        'name' => 'Bob Bobson',
                        'email' => 'b.bobson@outlook.com',
                        'phone' => '88888888888',
                    ],
                ],
                [ // output
                    [ // first group
                        [
                            'id' => 1,
                            'name' => 'Alice',
                            'email' => 'alice@hotmail.com',
                            'phone' => '9999999999',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Alice',
                            'email' => 'alice@outlook.com',
                            'phone' => '9999999999',
                        ],
                        [
                            'id' => 4,
                            'name' => 'Alice',
                            'email' => 'alice@outlook.com',
                            'phone' => '11111111111',
                        ]
                    ],
                    [ // second group
                        [
                            'id' => 3,
                            'name' => 'Bob',
                            'email' => 'bob@outlook.com',
                            'phone' => '88888888888',
                        ],
                        [
                            'id' => 6,
                            'name' => 'Bob Bobson',
                            'email' => 'b.bobson@outlook.com',
                            'phone' => '88888888888',
                        ]
                    ],
                    [ // third group
                        [
                            'id' => 5,
                            'name' => 'Claire',
                            'email' => 'claire@gmail.com',
                            'phone' => '777777777777',
                        ]
                    ]
                ]
            ]
        ];
    }
}

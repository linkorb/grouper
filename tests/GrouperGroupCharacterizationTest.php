<?php

namespace Grouper\Test;

use PHPUnit\Framework\TestCase;

use Grouper\Grouper;

class GrouperGroupCharacterizationTest extends TestCase
{
    /**
     * @covers \Grouper\Grouper::group
     */
    public function testGroupReturnsNoGroupsWhenThereAreNoRows()
    {
        $this->assertSame([], (new Grouper())->group([], ['name']));
    }

    /**
     * @covers \Grouper\Grouper::group
     */
    public function testGroupRejectsAnEmptyColumnList()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The columns argument cannot be an empty list.');

        (new Grouper())->group(
            [
                ['id' => 1, 'name' => 'Ada'],
            ],
            []
        );
    }

    /**
     * @covers \Grouper\Grouper::group
     */
    public function testGroupRejectsARowThatIsNotAnArray()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'All rows must be an associative array. There is at least one row which is not.'
        );

        (new Grouper())->group(['Ada'], ['name']);
    }

    /**
     * @covers \Grouper\Grouper::group
     */
    public function testGroupRejectsARowMissingTheKeyColumn()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'All rows must have an entry in the "id" column. There is at least one row which has not.'
        );

        (new Grouper())->group(
            [
                ['name' => 'Ada'],
            ],
            ['name']
        );
    }

    /**
     * @covers \Grouper\Grouper::group
     * @uses \Grouper\Group
     */
    public function testGroupClustersRowsThatShareTheOnlyColumn()
    {
        $ada = ['id' => 1, 'name' => 'Ada'];
        $adaAgain = ['id' => 2, 'name' => 'Ada'];
        $bea = ['id' => 3, 'name' => 'Bea'];

        $this->assertSame(
            [
                [$ada, $adaAgain],
                [$bea],
            ],
            (new Grouper())->group([$ada, $adaAgain, $bea], ['name'])
        );
    }

    /**
     * @covers \Grouper\Grouper::group
     * @uses \Grouper\Group
     */
    public function testGroupMergesRowsThatShareALaterColumn()
    {
        $first = ['id' => 1, 'email' => 'a@x', 'phone' => 'link'];
        $second = ['id' => 2, 'email' => 'b@x', 'phone' => 'link'];

        $this->assertSame(
            [
                [$first, $second],
            ],
            (new Grouper())->group([$first, $second], ['email', 'phone'])
        );
    }

    /**
     * An array cannot be a grouping key. PHP warns, that column is skipped
     * for the row, and the row still comes back in its own group.
     *
     * @covers \Grouper\Grouper::group
     * @uses \Grouper\Group
     */
    public function testGroupKeepsARowWhoseFirstColumnValueCannotBeAKey()
    {
        $kept = ['id' => 1, 'email' => 'a@x', 'phone' => 'keep'];
        $skipped = ['id' => 3, 'email' => ['not-scalar'], 'phone' => 'other'];
        $warnings = [];

        set_error_handler(function ($severity, $message) use (&$warnings) {
            $warnings[] = [$severity, $message];

            return true;
        });
        try {
            $grouped = (new Grouper())->group([$kept, $skipped], ['email', 'phone']);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([[E_WARNING, 'Illegal offset type']], $warnings);
        $this->assertSame(
            [
                [$kept],
                [$skipped],
            ],
            $grouped
        );
    }
}

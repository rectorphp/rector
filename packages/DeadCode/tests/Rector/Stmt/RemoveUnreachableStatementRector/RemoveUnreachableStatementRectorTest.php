<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveUnreachableStatementRector;

use Iterator;
use Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnreachableStatementRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/after_return.php.inc'];
        yield [__DIR__ . '/Fixture/after_throws.php.inc'];
        yield [__DIR__ . '/Fixture/keep_false_positive_always_true_if.php.inc'];
        yield [__DIR__ . '/Fixture/skip_condition_before.php.inc'];
        yield [__DIR__ . '/Fixture/skip_marked_skipped_test_file.php.inc'];
        yield [__DIR__ . '/Fixture/keep_comment.php.inc'];
        yield [__DIR__ . '/Fixture/if_else.php.inc'];
        yield [__DIR__ . '/Fixture/keep_false_positive_while.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveUnreachableStatementRector::class;
    }
}

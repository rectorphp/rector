<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector;

use Iterator;
use Rector\DeadCode\Rector\Stmt\RemoveDeadStmtRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadStmtRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadStmtRector::class;
    }
}

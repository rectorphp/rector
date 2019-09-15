<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Stmt\RemoveDeadStmtRector;

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

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadStmtRector::class;
    }
}

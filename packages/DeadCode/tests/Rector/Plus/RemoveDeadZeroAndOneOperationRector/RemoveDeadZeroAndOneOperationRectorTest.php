<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector;

use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadZeroAndOneOperationRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/multiple.php.inc'];
        yield [__DIR__ . '/Fixture/assigns.php.inc'];
        yield [__DIR__ . '/Fixture/skip_type_change.php.inc'];
        yield [__DIR__ . '/Fixture/skip_floats.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadZeroAndOneOperationRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\If_\RemoveAlwaysTrueIfConditionRector;

use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveAlwaysTrueIfConditionRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return RemoveAlwaysTrueIfConditionRector::class;
    }
}

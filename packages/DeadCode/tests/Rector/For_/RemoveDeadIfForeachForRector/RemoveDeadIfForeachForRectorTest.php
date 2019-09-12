<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\For_\RemoveDeadIfForeachForRector;

use Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadIfForeachForRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/side_effect_checks.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadIfForeachForRector::class;
    }
}

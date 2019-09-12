<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Foreach_\SimplifyForeachInstanceOfRector;

use Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyForeachInstanceOfRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return SimplifyForeachInstanceOfRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }
}

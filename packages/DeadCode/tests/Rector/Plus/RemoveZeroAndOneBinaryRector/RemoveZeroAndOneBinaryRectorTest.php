<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Plus\RemoveZeroAndOneBinaryRector;

use Iterator;
use Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveZeroAndOneBinaryRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/assigns.php.inc'];
        yield [__DIR__ . '/Fixture/no_unintended.php.inc'];
        yield [__DIR__ . '/Fixture/skip_type_change.php.inc'];
        yield [__DIR__ . '/Fixture/skip_floats.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveZeroAndOneBinaryRector::class;
    }
}

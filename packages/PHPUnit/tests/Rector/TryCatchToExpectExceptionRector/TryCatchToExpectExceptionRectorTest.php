<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\TryCatchToExpectExceptionRector;

use Rector\PHPUnit\Rector\TryCatchToExpectExceptionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TryCatchToExpectExceptionRectorTest extends AbstractRectorTestCase
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
        return TryCatchToExpectExceptionRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }
}

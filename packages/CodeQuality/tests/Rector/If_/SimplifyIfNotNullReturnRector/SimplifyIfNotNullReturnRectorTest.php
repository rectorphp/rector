<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfNotNullReturnRector;

use Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyIfNotNullReturnRectorTest extends AbstractRectorTestCase
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
        return SimplifyIfNotNullReturnRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/skip.php.inc'];
    }
}

<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Unset_\UnsetCastRector;

use Rector\Php\Rector\Unset_\UnsetCastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UnsetCastRectorTest extends AbstractRectorTestCase
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
        return UnsetCastRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }
}

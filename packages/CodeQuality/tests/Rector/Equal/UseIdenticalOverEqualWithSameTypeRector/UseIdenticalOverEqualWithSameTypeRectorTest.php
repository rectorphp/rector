<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;

use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UseIdenticalOverEqualWithSameTypeRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_objects.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return UseIdenticalOverEqualWithSameTypeRector::class;
    }
}

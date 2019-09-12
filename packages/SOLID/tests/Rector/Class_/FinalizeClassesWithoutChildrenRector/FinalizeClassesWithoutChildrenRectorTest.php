<?php declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Class_\FinalizeClassesWithoutChildrenRector;

use Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FinalizeClassesWithoutChildrenRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip.php.inc'];
        yield [__DIR__ . '/Fixture/entity.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return FinalizeClassesWithoutChildrenRector::class;
    }
}

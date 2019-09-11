<?php declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector;

use Rector\SOLID\Rector\Class_\MakeUnusedClassesWithChildrenAbstractRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MakeUnusedClassesWithChildrenAbstractRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_new.php.inc'];
        yield [__DIR__ . '/Fixture/skip_static_call.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return MakeUnusedClassesWithChildrenAbstractRector::class;
    }
}

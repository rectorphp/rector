<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadConstructorRector;

use Rector\DeadCode\Rector\ClassMethod\RemoveDeadConstructorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadConstructorRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_private.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadConstructorRector::class;
    }
}

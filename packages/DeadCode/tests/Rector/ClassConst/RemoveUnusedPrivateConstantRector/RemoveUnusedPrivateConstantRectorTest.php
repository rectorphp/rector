<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassConst\RemoveUnusedPrivateConstantRector;

use Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedPrivateConstantRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/keep_constant.php.inc'];
        yield [__DIR__ . '/Fixture/keep_static_constant.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedPrivateConstantRector::class;
    }
}

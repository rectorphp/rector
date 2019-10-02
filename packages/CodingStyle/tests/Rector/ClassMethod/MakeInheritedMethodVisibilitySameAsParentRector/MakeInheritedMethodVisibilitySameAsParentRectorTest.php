<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;

use Iterator;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MakeInheritedMethodVisibilitySameAsParentRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/private.php.inc'];
        yield [__DIR__ . '/Fixture/skip_existing.php.inc'];
        yield [__DIR__ . '/Fixture/skip_something.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return MakeInheritedMethodVisibilitySameAsParentRector::class;
    }
}

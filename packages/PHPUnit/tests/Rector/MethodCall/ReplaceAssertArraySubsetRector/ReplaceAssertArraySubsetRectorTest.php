<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector;

use Iterator;
use Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReplaceAssertArraySubsetRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/issue_2069.php.inc'];
        yield [__DIR__ . '/Fixture/issue_2237.php.inc'];
        yield [__DIR__ . '/Fixture/multilevel_array.php.inc'];
        yield [__DIR__ . '/Fixture/variable.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ReplaceAssertArraySubsetRector::class;
    }
}

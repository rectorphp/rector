<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector;

use Rector\Php\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringClassNameToClassConstantRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/pre_slash.php.inc'];
        yield [__DIR__ . '/Fixture/skip_error.php.inc'];
        yield [__DIR__ . '/Fixture/skip_sensitive.php.inc'];
        yield [__DIR__ . '/Fixture/skip_slashes.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return StringClassNameToClassConstantRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;

use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CallableThisArrayToAnonymousFunctionRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/another_class.php.inc'];
        yield [__DIR__ . '/Fixture/skip_another_class.php.inc'];
        yield [__DIR__ . '/Fixture/skip_empty_first_array.php.inc'];
        yield [__DIR__ . '/Fixture/skip_as_well.php.inc'];
        yield [__DIR__ . '/Fixture/no_return_for_void.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return CallableThisArrayToAnonymousFunctionRector::class;
    }
}

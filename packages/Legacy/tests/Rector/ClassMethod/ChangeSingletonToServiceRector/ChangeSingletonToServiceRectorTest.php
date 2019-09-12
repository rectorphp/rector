<?php declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector;

use Rector\Legacy\Rector\ClassMethod\ChangeSingletonToServiceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeSingletonToServiceRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/static_variable.php.inc'];
        yield [__DIR__ . '/Fixture/protected_construct.php.inc'];
        yield [__DIR__ . '/Fixture/non_empty_protected_construct.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ChangeSingletonToServiceRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExceptionToErrorEventConstantRector;

use Iterator;
use Rector\Symfony\Rector\Console\ConsoleExceptionToErrorEventConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConsoleExceptionToErrorEventConstantRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return ConsoleExceptionToErrorEventConstantRector::class;
    }
}

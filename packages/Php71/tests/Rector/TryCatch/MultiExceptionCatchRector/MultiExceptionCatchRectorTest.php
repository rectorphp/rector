<?php declare(strict_types=1);

namespace Rector\Php71\Tests\Rector\TryCatch\MultiExceptionCatchRector;

use Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MultiExceptionCatchRectorTest extends AbstractRectorTestCase
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
    }

    protected function getRectorClass(): string
    {
        return MultiExceptionCatchRector::class;
    }
}

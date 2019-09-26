<?php declare(strict_types=1);

namespace Rector\Php54\Tests\Rector\Break_\RemoveZeroBreakContinueRector;

use Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveZeroBreakContinueRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        // to prevent loading PHP 5.4+ invalid code
        $this->doTestFileWithoutAutoload($file);
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
        return RemoveZeroBreakContinueRector::class;
    }
}

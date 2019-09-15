<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Switch_\ReduceMultipleDefaultSwitchRector;

use Rector\Php\Rector\Switch_\ReduceMultipleDefaultSwitchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReduceMultipleDefaultSwitchRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFileWithoutAutoload($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/hidden_in_middle.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ReduceMultipleDefaultSwitchRector::class;
    }
}

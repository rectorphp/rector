<?php declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\ConstFetch\BarewordStringRector;

use Iterator;
use Rector\Php72\Rector\ConstFetch\BarewordStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class BarewordStringRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFileWithoutAutoload($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/define.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return BarewordStringRector::class;
    }
}

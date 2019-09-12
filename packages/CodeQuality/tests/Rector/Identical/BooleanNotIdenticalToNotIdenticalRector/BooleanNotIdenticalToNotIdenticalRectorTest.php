<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;

use Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class BooleanNotIdenticalToNotIdenticalRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/just_first.php.inc'];
        yield [__DIR__ . '/Fixture/keep.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return BooleanNotIdenticalToNotIdenticalRector::class;
    }
}

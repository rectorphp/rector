<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector;

use Iterator;
use Rector\CodingStyle\Rector\Identical\IdenticalFalseToBooleanNotRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IdenticalFalseToBooleanNotRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_null_false.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return IdenticalFalseToBooleanNotRector::class;
    }
}

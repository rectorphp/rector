<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\BinaryOp\SimplifyDeMorganBinaryRector;

use Rector\CodeQuality\Rector\BinaryOp\SimplifyDeMorganBinaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyDeMorganBinaryRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/binary_and.php.inc'];
        yield [__DIR__ . '/Fixture/multi_binary.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SimplifyDeMorganBinaryRector::class;
    }
}

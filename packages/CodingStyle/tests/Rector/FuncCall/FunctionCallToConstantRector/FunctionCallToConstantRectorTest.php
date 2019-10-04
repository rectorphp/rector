<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionCallToConstantRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/version-compare.php.inc'];
        yield [__DIR__ . '/Fixture/sapi-name.php.inc'];
        yield [__DIR__ . '/Fixture/pi.php.inc'];
        yield [__DIR__ . '/Fixture/skip-version-compare.php.inc'];

    }

    protected function getRectorClass(): string
    {
        return FunctionCallToConstantRector::class;
    }
}

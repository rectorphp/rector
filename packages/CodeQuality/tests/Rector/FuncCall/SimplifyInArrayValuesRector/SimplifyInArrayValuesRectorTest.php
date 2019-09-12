<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyInArrayValuesRector;

use Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyInArrayValuesRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function getRectorClass(): string
    {
        return SimplifyInArrayValuesRector::class;
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }
}

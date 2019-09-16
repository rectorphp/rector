<?php declare(strict_types=1);

namespace Rector\Celebrity\Tests\Rector\FuncCall\SetTypeToCastRector;

use Rector\Celebrity\Rector\FuncCall\SetTypeToCastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SetTypeToCastRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/assign.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SetTypeToCastRector::class;
    }
}

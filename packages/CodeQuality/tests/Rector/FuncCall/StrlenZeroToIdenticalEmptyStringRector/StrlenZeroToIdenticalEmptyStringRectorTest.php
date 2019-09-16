<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\StrlenZeroToIdenticalEmptyStringRector;

use Rector\CodeQuality\Rector\FuncCall\StrlenZeroToIdenticalEmptyStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StrlenZeroToIdenticalEmptyStringRectorTest extends AbstractRectorTestCase
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
        return StrlenZeroToIdenticalEmptyStringRector::class;
    }
}

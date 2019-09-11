<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector;

use Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SplitGroupedConstantsAndPropertiesRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return SplitGroupedConstantsAndPropertiesRector::class;
    }
}

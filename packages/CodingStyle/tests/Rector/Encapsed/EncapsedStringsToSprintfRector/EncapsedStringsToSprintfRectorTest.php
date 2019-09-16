<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Encapsed\EncapsedStringsToSprintfRector;

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class EncapsedStringsToSprintfRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/numberz.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return EncapsedStringsToSprintfRector::class;
    }
}

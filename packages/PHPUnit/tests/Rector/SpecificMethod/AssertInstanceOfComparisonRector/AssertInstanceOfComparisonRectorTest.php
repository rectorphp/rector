<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertInstanceOfComparisonRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertInstanceOfComparisonRectorTest extends AbstractRectorTestCase
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
        return AssertInstanceOfComparisonRector::class;
    }
}

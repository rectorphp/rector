<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\LogicalAnd\AndAssignsToSeparateLinesRector;

use Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AndAssignsToSeparateLinesRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/keep_in_condition.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return AndAssignsToSeparateLinesRector::class;
    }
}

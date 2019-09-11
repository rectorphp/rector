<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Assign\CombinedAssignRector;

use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Some tests used from:
 * - https://github.com/doctrine/coding-standard/pull/83/files
 * - https://github.com/slevomat/coding-standard/blob/master/tests/Sniffs/Operators/data/requireCombinedAssignmentOperatorErrors.php
 */
final class CombinedAssignRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return CombinedAssignRector::class;
    }
}

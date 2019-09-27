<?php declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\FuncCall\IsObjectOnIncompleteClassRector;

use Iterator;
use Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IsObjectOnIncompleteClassRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return IsObjectOnIncompleteClassRector::class;
    }
}

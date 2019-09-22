<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\IsObjectOnIncompleteClassRector;

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

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return IsObjectOnIncompleteClassRector::class;
    }
}

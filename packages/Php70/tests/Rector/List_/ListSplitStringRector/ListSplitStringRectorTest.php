<?php declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\List_\ListSplitStringRector;

use Rector\Php70\Rector\List_\ListSplitStringRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ListSplitStringRectorTest extends AbstractRectorTestCase
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
        return ListSplitStringRector::class;
    }
}

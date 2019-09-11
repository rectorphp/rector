<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector;

use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDeadReturnRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/keep.php.inc'];
        yield [__DIR__ . '/Fixture/keep_comment_under.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDeadReturnRector::class;
    }
}

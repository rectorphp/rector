<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\StaticCall\RemoveParentCallWithoutParentRector;

use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveParentCallWithoutParentRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/parent_but_no_method.php.inc'];
        yield [__DIR__ . '/Fixture/skip_trait.php.inc'];
        yield [__DIR__ . '/Fixture/edge_case.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveParentCallWithoutParentRector::class;
    }
}

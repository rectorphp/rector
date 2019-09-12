<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector;

use Rector\Php\Rector\FuncCall\RemoveMissingCompactVariableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveMissingCompactVariableRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/empty_compact.php.inc'];
        yield [__DIR__ . '/Fixture/skip_maybe_defined.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveMissingCompactVariableRector::class;
    }
}

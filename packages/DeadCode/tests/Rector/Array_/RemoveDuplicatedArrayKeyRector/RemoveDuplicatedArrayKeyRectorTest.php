<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Array_\RemoveDuplicatedArrayKeyRector;

use Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDuplicatedArrayKeyRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/last_man_standing.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDuplicatedArrayKeyRector::class;
    }
}

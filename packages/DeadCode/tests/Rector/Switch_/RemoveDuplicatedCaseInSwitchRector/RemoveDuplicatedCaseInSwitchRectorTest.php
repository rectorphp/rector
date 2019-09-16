<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;

use Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDuplicatedCaseInSwitchRectorTest extends AbstractRectorTestCase
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
        return RemoveDuplicatedCaseInSwitchRector::class;
    }
}

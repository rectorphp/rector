<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\TestListenerToHooksRector;

use Iterator;
use Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TestListenerToHooksRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/clear_it_all.php.inc'];
        yield [__DIR__ . '/Fixture/before_list_hook.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return TestListenerToHooksRector::class;
    }
}

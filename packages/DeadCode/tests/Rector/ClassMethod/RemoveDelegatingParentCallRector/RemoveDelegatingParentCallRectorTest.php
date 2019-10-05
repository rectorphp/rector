<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector;

use Iterator;
use Rector\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveDelegatingParentCallRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/skip_access_override.php.inc'];
        yield [__DIR__ . '/Fixture/skip_extra_arguments.php.inc'];
        yield [__DIR__ . '/Fixture/skip_extra_content.php.inc'];
        yield [__DIR__ . '/Fixture/skip_in_trait.php.inc'];
        yield [__DIR__ . '/Fixture/skip_different_method_name.php.inc'];
        yield [__DIR__ . '/Fixture/skip_changed_arguments.php.inc'];
        yield [__DIR__ . '/Fixture/skip_required.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RemoveDelegatingParentCallRector::class;
    }
}

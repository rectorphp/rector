<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\UseSpecificWillMethodRector;

use Rector\PHPUnit\Rector\MethodCall\UseSpecificWillMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UseSpecificWillMethodRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/will.php.inc'];
        yield [__DIR__ . '/Fixture/with.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return UseSpecificWillMethodRector::class;
    }
}

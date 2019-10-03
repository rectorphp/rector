<?php
declare(strict_types=1);

namespace Rector\Tests\Exclusion\Check;

use Iterator;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\Plus\RemoveZeroAndOneBinaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExcludeByDocBlockTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/docblock-on-self-baseline.php.inc'];
        yield [__DIR__ . '/Fixture/docblock-on-self-no-rector.php.inc'];
        yield [__DIR__ . '/Fixture/docblock-on-parent-baseline.php.inc'];
        yield [__DIR__ . '/Fixture/docblock-on-parent-norector.php.inc'];
        yield [__DIR__ . '/Fixture/other-docblocks.php.inc'];
    }

    protected function provideConfig(): string
    {
        return dirname(__DIR__) . '/config.yaml';
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveEmptyClassMethodRector::class => [],
            RemoveZeroAndOneBinaryRector::class => [],
        ];
    }
}

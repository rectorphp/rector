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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture-norector.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2-norector.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
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

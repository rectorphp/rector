<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Each;

use Rector\Php72\Rector\Each\ListEachRector;
use Rector\Php72\Rector\Each\WhileEachToForeachRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Test battery inspired by:
 * - https://stackoverflow.com/q/46492621/1348344 + Drupal refactorings
 * - https://stackoverflow.com/a/51278641/1348344
 */
final class EachRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            WhileEachToForeachRector::class => [],
            ListEachRector::class => [],
        ];
    }
}

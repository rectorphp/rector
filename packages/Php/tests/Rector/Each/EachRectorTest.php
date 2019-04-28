<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Each;

use Rector\Php\Rector\Each\ListEachRector;
use Rector\Php\Rector\Each\WhileEachToForeachRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Test battery inspired by:
 * - https://stackoverflow.com/q/46492621/1348344 + Drupal refactorings
 * - https://stackoverflow.com/a/51278641/1348344
 */
final class EachRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
        ]);
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

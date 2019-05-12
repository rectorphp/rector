<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector;

use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ForToForeachRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/for_with_count.php.inc',
            __DIR__ . '/Fixture/for_with_switched_compare.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ForToForeachRector::class;
    }
}

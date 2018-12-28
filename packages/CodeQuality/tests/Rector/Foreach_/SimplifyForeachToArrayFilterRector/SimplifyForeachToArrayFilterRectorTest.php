<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToArrayFilterRector;

use Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyForeachToArrayFilterRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/skip.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SimplifyForeachToArrayFilterRector::class;
    }
}

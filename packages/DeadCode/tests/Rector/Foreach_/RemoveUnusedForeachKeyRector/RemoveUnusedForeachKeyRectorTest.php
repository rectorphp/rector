<?php declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Foreach_\RemoveUnusedForeachKeyRector;

use Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RemoveUnusedForeachKeyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RemoveUnusedForeachKeyRector::class;
    }
}

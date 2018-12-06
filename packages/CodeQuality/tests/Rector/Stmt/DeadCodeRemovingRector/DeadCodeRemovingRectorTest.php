<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Stmt\DeadCodeRemovingRector;

use Rector\CodeQuality\Rector\Stmt\DeadCodeRemovingRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class DeadCodeRemovingRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    public function getRectorClass(): string
    {
        return DeadCodeRemovingRector::class;
    }
}

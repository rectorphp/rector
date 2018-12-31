<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfElseToTernaryRector;

use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyIfElseToTernaryRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/keep.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SimplifyIfElseToTernaryRector::class;
    }
}

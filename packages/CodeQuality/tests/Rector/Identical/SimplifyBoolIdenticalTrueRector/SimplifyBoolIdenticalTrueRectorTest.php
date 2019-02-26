<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyBoolIdenticalTrueRector;

use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyBoolIdenticalTrueRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/directly.php.inc', __DIR__ . '/Fixture/negate.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SimplifyBoolIdenticalTrueRector::class;
    }
}

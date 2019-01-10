<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;

use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConsecutiveNullCompareReturnsToNullCoalesceQueueRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/triplets.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class;
    }
}

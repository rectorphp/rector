<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfNotNullReturnRector;

use Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyIfNotNullReturnRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SimplifyIfNotNullReturnRector::class;
    }
}

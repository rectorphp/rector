<?php declare(strict_types=1);

namespace Rector\Celebrity\Tests\Rector\FuncCall\ConsistentImplodeRector;

use Rector\Celebrity\Rector\FuncCall\ConsistentImplodeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConsistentImplodeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ConsistentImplodeRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentImplodeRector;

use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
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

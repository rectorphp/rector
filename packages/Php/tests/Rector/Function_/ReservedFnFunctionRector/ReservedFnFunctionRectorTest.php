<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Function_\ReservedFnFunctionRector;

use Rector\Php\Rector\Function_\ReservedFnFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReservedFnFunctionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ReservedFnFunctionRector::class;
    }
}

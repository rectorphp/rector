<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\IsAWithStringWithThirdArgumentRector;

use Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IsAWithStringWithThirdArgumentRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return IsAWithStringWithThirdArgumentRector::class;
    }
}

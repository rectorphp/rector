<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector;

use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CompactToVariablesRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return CompactToVariablesRector::class;
    }
}

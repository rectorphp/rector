<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Ternary\TernaryToElvisRector;

use Rector\CodeQuality\Rector\Ternary\TernaryToElvisRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TernaryToElvisRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return TernaryToElvisRector::class;
    }
}

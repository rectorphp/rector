<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Double\RealToFloatTypeCastRector;

use Rector\Php\Rector\Double\RealToFloatTypeCastRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RealToFloatTypeCastRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RealToFloatTypeCastRector::class;
    }
}

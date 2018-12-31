<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SingleInArrayToCompareRector;

use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SingleInArrayToCompareRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SingleInArrayToCompareRector::class;
    }
}

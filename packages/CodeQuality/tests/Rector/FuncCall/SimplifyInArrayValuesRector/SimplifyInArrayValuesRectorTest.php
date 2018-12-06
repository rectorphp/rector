<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\SimplifyInArrayValuesRector;

use Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyInArrayValuesRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SimplifyInArrayValuesRector::class;
    }
}

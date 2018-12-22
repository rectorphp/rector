<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassConst\CompleteVarDocTypeConstantRector;

use Rector\CodingStyle\Rector\ClassConst\CompleteVarDocTypeConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CompleteVarDocTypeConstantRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return CompleteVarDocTypeConstantRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Property\CompleteVarDocTypePropertyRector;

use Rector\Php\Rector\Property\CompleteVarDocTypePropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CompleteVarDocTypePropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/property_assign.php.inc',
            __DIR__ . '/Fixture/default_value.php.inc',
            __DIR__ . '/Fixture/assign_conflict.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return CompleteVarDocTypePropertyRector::class;
    }
}

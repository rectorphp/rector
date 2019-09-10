<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;

final class AddArrayParamDocTypeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/from_property.php.inc',
            __DIR__ . '/Fixture/from_getter.php.inc',
            __DIR__ . '/Fixture/edge_case.php.inc',
            // keep
            __DIR__ . '/Fixture/keep_mixed.php.inc',
            __DIR__ . '/Fixture/keep_filled.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AddArrayParamDocTypeRector::class;
    }
}

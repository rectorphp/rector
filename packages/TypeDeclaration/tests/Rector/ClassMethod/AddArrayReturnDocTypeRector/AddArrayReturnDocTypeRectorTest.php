<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;

final class AddArrayReturnDocTypeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/setter_based.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return AddArrayReturnDocTypeRector::class;
    }
}

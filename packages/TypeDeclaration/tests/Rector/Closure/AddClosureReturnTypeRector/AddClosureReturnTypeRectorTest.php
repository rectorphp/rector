<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\Closure\AddClosureReturnTypeRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

final class AddClosureReturnTypeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/return_type_object.php.inc',
            __DIR__ . '/Fixture/callable_false_positive.php.inc',
            __DIR__ . '/Fixture/subtype_of_object.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AddClosureReturnTypeRector::class;
    }
}

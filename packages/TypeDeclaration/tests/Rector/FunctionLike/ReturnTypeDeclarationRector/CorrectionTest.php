<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

final class CorrectionTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/Correction/constructor_property_assign_over_getter.php.inc',
            __DIR__ . '/Fixture/Correction/prefix_fqn.php.inc',
            // skip
            __DIR__ . '/Fixture/Correction/skip_override_of_the_same_class.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }
}

<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult;

final class FullyQualifiedName
{
    public function isValidDataProvider(): array
    {
        return [
            new ValidationResult(), true,
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult;

final class FullyQualifiedName
{
    /**
     * @return bool[]|\Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult[]
     */
    public function isValidDataProvider(): array
    {
        return [
            new ValidationResult(), true,
        ];
    }
}

?>

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
     * @return \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult[]|bool[]
     */
    public function isValidDataProvider(): array
    {
        return [
            new ValidationResult(), true,
        ];
    }
}

?>

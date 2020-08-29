<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult;

final class FullyQualifiedName
{
    /**
     * @return \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult[]|bool[]
     */
    public function isValidDataProvider(): array
    {
        return [
            new ValidationResult(), true,
        ];
    }
}

?>

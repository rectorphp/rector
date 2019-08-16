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

    public function getValidationErrorMessagesAsStringDataProvider(): array
    {
        return [
            'no_errors' => [new ValidationResult(), 'ha_ja'],
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

    /**
     * @return string[][]|\Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult[][]
     */
    public function getValidationErrorMessagesAsStringDataProvider(): array
    {
        return [
            'no_errors' => [new ValidationResult(), 'ha_ja'],
        ];
    }
}

?>

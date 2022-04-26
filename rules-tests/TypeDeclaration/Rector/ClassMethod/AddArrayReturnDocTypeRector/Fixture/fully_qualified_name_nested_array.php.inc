<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult;

final class FullyQualifiedNameNestedArray
{
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

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult;

final class FullyQualifiedNameNestedArray
{
    /**
     * @return array<string, array<\Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult|string>>
     */
    public function getValidationErrorMessagesAsStringDataProvider(): array
    {
        return [
            'no_errors' => [new ValidationResult(), 'ha_ja'],
        ];
    }
}

?>

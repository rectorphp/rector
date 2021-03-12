<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult;

final class FullyQualifiedNameNestedArray
{
    /**
     * @return array<string, array<string|\Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ValidationResult>>
     */
    public function getValidationErrorMessagesAsStringDataProvider(): array
    {
        return [
            'no_errors' => [new ValidationResult(), 'ha_ja'],
        ];
    }
}

?>

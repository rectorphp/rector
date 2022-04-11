<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIsMissingNullWithNestedGenericSyntax
{
    /**
     * @var array<int, array<string, \DateTime>>
     */
    private ?array $dateTimes;
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIsMissingNullWithNestedGenericSyntax
{
    /**
     * @var array<int, array<string, \DateTime>>|null
     */
    private ?array $dateTimes;
}
?>

<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIsMissingNullWithGenericSyntax
{
    /**
     * Rector automatically transforms generic int-keyed simple arrays to the [] notation
     * @see \Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper::isIntegerKeyAndNonNestedArray
     *
     * @var array<int,\DateTime>
     */
    private ?array $dateTimes;
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIsMissingNullWithGenericSyntax
{
    /**
     * Rector automatically transforms generic int-keyed simple arrays to the [] notation
     * @see \Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper::isIntegerKeyAndNonNestedArray
     *
     * @var \DateTime[]|null
     */
    private ?array $dateTimes;
}
?>

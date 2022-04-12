<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnAnnotationIsMissingNullWithGenericSyntax
{
    /**
     * Rector automatically transforms generic int-keyed simple arrays to the [] notation
     * @see \Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper::isIntegerKeyAndNonNestedArray
     *
     * @return array<int,\DateTime>
     */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnAnnotationIsMissingNullWithGenericSyntax
{
    /**
     * Rector automatically transforms generic int-keyed simple arrays to the [] notation
     * @see \Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper::isIntegerKeyAndNonNestedArray
     *
     * @return \DateTime[]|null
     */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
    }
}
?>

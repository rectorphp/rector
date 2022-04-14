<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIsMissingNullWithGenericSyntax
{
    /**
     * Rector automatically transforms generic int-keyed simple arrays to the [] notation
     * @see \Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper::isIntegerKeyAndNonNestedArray
     *
     * @param array<int,\DateTime> $dateTimes
     */
    public function setDateTimes(?array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIsMissingNullWithGenericSyntax
{
    /**
     * Rector automatically transforms generic int-keyed simple arrays to the [] notation
     * @see \Rector\PHPStanStaticTypeMapper\TypeMapper\ArrayTypeMapper::isIntegerKeyAndNonNestedArray
     *
     * @param \DateTime[]|null $dateTimes
     */
    public function setDateTimes(?array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
?>

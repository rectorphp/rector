<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIsMissingNullWithNestedGenericSyntax
{
    /**
     * @param array<int, array<string, \DateTime>> $dateTimes
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

final class ParamAnnotationIsMissingNullWithNestedGenericSyntax
{
    /**
     * @param array<int, array<string, \DateTime>>|null $dateTimes
     */
    public function setDateTimes(?array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
?>

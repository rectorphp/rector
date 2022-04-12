<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnAnnotationIsMissingNullWithNestedGenericSyntax
{
    /**
     * @return array<int, array<string, \DateTime>>
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

final class ReturnAnnotationIsMissingNullWithNestedGenericSyntax
{
    /**
     * @return array<int, array<string, \DateTime>>|null
     */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
    }
}
?>

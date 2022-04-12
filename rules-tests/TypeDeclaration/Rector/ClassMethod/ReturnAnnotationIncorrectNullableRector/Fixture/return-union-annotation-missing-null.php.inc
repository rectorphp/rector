<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnUnionAnnotationMissingNull
{
    /**
     * @return \DateTime[]|\DateTimeImmutable[]
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

final class ReturnUnionAnnotationMissingNull
{
    /**
     * @return \DateTime[]|\DateTimeImmutable[]|null
     */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
    }
}
?>

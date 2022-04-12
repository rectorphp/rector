<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnUnionAnnotationIncorrectlyIncludesNull
{
    /**
     * @return \DateTime[]|\DateTimeImmutable[]|null
     */
    public function getDateTimes(): array
    {
        return $this->dateTimes;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnUnionAnnotationIncorrectlyIncludesNull
{
    /**
     * @return \DateTime[]|\DateTimeImmutable[]
     */
    public function getDateTimes(): array
    {
        return $this->dateTimes;
    }
}
?>

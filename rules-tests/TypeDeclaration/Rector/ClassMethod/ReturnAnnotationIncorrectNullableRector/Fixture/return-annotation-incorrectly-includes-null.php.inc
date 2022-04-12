<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnAnnotationIncorrectlyIncludesNull
{
    /**
     * @return \DateTime[]|null
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

final class ReturnAnnotationIncorrectlyIncludesNull
{
    /**
     * @return \DateTime[]
     */
    public function getDateTimes(): array
    {
        return $this->dateTimes;
    }
}
?>

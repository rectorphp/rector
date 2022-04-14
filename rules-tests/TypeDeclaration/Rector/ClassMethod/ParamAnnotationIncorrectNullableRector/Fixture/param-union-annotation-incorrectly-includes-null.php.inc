<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamUnionAnnotationIncorrectlyIncludesNull
{
    /**
     * @param \DateTime[]|\DateTimeImmutable[]|null $dateTimes
     */
    public function setDateTimes(array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamUnionAnnotationIncorrectlyIncludesNull
{
    /**
     * @param \DateTime[]|\DateTimeImmutable[] $dateTimes
     */
    public function setDateTimes(array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
?>

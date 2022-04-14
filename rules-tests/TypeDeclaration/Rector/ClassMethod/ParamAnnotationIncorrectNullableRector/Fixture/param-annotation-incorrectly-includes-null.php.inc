<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIncorrectlyIncludesNull
{
    /**
     * @param \DateTime[]|null $dateTimes
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

final class ParamAnnotationIncorrectlyIncludesNull
{
    /**
     * @param \DateTime[] $dateTimes
     */
    public function setDateTimes(array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
?>

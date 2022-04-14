<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIsMissingNullWithDefaultNull
{
    /**
     * @param \DateTime[] $dateTimes
     */
    public function setDateTimes(array $dateTimes = null): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIsMissingNullWithDefaultNull
{
    /**
     * @param \DateTime[]|null $dateTimes
     */
    public function setDateTimes(array $dateTimes = null): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
?>

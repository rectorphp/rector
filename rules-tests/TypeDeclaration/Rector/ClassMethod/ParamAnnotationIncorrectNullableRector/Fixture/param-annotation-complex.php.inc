<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationComplex
{
    /**
     * @Serializer\VirtualProperty
     * @Serializer\Type("array<DateTime>")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @AppAssert\Country,
     * })
     * @param \DateTime[] $dateTimes
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

final class ParamAnnotationComplex
{
    /**
     * @Serializer\VirtualProperty
     * @Serializer\Type("array<DateTime>")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @AppAssert\Country,
     * })
     * @param \DateTime[]|null $dateTimes
     */
    public function setDateTimes(?array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
?>

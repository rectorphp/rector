<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIsMissingNullMultiple
{
    /**
     * @param \DateTime[] $dateTimes
     * @param array<\DateTimeImmutable> $immutableDateTimes
     * @param array<\DateTimeInterface>|null $dateTimeInterfaces
     */
    public function setDateTimes(?array $dateTimes, ?array $immutableDateTimes, ?array $dateTimeInterfaces): self
    {
        $this->dateTimes = $dateTimes;
        $this->immutableDateTimes = $immutableDateTimes;
        $this->dateTimeInterfaces = $dateTimeInterfaces;

        return $this;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIsMissingNullMultiple
{
    /**
     * @param \DateTime[]|null $dateTimes
     * @param \DateTimeImmutable[]|null $immutableDateTimes
     * @param array<\DateTimeInterface>|null $dateTimeInterfaces
     */
    public function setDateTimes(?array $dateTimes, ?array $immutableDateTimes, ?array $dateTimeInterfaces): self
    {
        $this->dateTimes = $dateTimes;
        $this->immutableDateTimes = $immutableDateTimes;
        $this->dateTimeInterfaces = $dateTimeInterfaces;

        return $this;
    }
}
?>

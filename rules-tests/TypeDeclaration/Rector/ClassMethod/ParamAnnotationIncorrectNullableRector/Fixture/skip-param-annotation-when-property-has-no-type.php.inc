<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class SkipParamAnnotationWhenPropertyHasNoType
{
    /**
     * @param \DateTime[] $dateTimes
     */
    public function setDateTimes($dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}

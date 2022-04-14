<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class SkipParamAnnotationWithDefaultNull
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

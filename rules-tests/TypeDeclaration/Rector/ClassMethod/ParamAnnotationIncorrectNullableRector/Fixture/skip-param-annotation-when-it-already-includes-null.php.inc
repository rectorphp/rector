<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class SkipParamAnnotationWhenItAlreadyIncludesNull
{
    /**
     * @param \DateTime[]|null $dateTimes
     */
    public function setDateTimes(?array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}

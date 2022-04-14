<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class SkipBrokenParamAnnotation
{
    /** @param 'week'|'month'|'year' $dateTimes */
    public function setDateTimes(string $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}

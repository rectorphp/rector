<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class SkipReturnAnnotationWhenPropertyHasNonNullableType
{
    /**
     * @return \DateTime[]
     */
    public function getDateTimes(): array
    {
        return $this->dateTimes;
    }
}

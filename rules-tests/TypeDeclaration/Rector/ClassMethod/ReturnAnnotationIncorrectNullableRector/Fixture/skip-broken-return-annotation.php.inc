<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class SkipBrokenReturnAnnotation
{
    /** @return # */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
    }
}

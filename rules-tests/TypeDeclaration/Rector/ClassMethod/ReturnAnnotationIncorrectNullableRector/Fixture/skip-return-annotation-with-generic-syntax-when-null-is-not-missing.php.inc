<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class SkipReturnAnnotationWithGenericSyntaxWhenNullIsNotMissing
{
    /**
     * @return  array<int,\DateTime>|null $dateTimes
     */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
    }
}

<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnAnnotationAlreadyIncludesNull
{
    /**
     * @return \DateTime[]|null
     */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
    }
}

<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class SkipReturnAnnotationWhenMethodHasNoReturnType
{
    /**
     * @return \DateTime[]
     */
    public function getDateTimes()
    {
        return $this->dateTimes;
    }
}

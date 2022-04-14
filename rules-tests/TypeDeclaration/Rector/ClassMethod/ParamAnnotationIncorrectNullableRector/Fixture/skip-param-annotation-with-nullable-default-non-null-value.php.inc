<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class SkipParamAnnotationWithDefaultNull
{
    /**
     * @param bool|null $flag
     */
    public function setDateTimes(?bool $flag = true): self
    {
        $this->flag = $flag;

        return $this;
    }
}

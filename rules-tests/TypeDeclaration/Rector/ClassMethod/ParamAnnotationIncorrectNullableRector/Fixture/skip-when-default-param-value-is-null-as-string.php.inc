<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class SkipWhenDefaultParamValueIsNullAsString
{
    /**
     * @param string $text
     */
    public function setDateTimes(string $text = 'null'): self
    {
        $this->$text = $text;

        return $this;
    }
}

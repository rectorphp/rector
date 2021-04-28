<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class SkipChangedType
{
    private array $kind;

    public function setAge($kind)
    {
        if (!is_array($kind)) {
            $kind = [$kind];
        }

        $this->kind = $kind;
    }
}

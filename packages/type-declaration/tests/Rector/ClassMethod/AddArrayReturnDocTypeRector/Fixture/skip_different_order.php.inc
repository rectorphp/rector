<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class Uri
{
    public function __construct($value)
    {
    }
}

class SkipDifferentOrder
{
    /**
     * @param mixed $value
     * @return string|\Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture\Uri|null
     */
    public function changeValue($value){
        if (null === $value || '' === $value) {
            return $value;
        }

        return new Uri($value);
    }
}

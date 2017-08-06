<?php declare (strict_types=1);

class ClassWithExternalConstant
{
    public function getValue()
    {
        return ClassWithConstants::NEW_CONSTANT;
    }
}

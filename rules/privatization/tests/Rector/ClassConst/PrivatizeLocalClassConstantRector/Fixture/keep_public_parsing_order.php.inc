<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

// The class using the constant must be parsed first
class ForeignConstantAddictUser2
{
    public function run()
    {
        return ClassWithConstantUsedSomewhereElse2::NON_LOCAL_ONLY;
    }
}

class ClassWithConstantUsedSomewhereElse2Parent
{
}

// The class declaring the constant must have a superclass, so that rector must find out which one declares the constant
class ClassWithConstantUsedSomewhereElse2 extends ClassWithConstantUsedSomewhereElse2Parent
{
    const NON_LOCAL_ONLY = true;
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

// The class using the constant must be parsed first
class ForeignConstantAddictUser2
{
    public function run()
    {
        return ClassWithConstantUsedSomewhereElse2::NON_LOCAL_ONLY;
    }
}

class ClassWithConstantUsedSomewhereElse2Parent
{
}

// The class declaring the constant must have a superclass, so that rector must find out which one declares the constant
class ClassWithConstantUsedSomewhereElse2 extends ClassWithConstantUsedSomewhereElse2Parent
{
    public const NON_LOCAL_ONLY = true;
}

?>

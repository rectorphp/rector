<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class ClassWithConstantUsedSomewhereElse
{
    const NON_LOCAL_ONLY = true;
}

class ForeignConstantAddictUser
{
    public function run()
    {
        return ClassWithConstantUsedSomewhereElse::NON_LOCAL_ONLY;
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class ClassWithConstantUsedSomewhereElse
{
    public const NON_LOCAL_ONLY = true;
}

class ForeignConstantAddictUser
{
    public function run()
    {
        return ClassWithConstantUsedSomewhereElse::NON_LOCAL_ONLY;
    }
}

?>

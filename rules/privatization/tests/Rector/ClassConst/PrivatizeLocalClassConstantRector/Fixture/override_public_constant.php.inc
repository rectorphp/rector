<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class MiddleExtendingClass extends DeclaringPublicConstantClass
{
}

class OverridingPublicConstantClass extends MiddleExtendingClass
{
    const PUBLIC_CONSTANT = false;
}

class DeclaringPublicConstantClass
{
    const PUBLIC_CONSTANT = true;
}

class PublicConstantUser
{
    public function run()
    {
        return DeclaringPublicConstantClass::PUBLIC_CONSTANT;
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class MiddleExtendingClass extends DeclaringPublicConstantClass
{
}

class OverridingPublicConstantClass extends MiddleExtendingClass
{
    public const PUBLIC_CONSTANT = false;
}

class DeclaringPublicConstantClass
{
    public const PUBLIC_CONSTANT = true;
}

class PublicConstantUser
{
    public function run()
    {
        return DeclaringPublicConstantClass::PUBLIC_CONSTANT;
    }
}

?>

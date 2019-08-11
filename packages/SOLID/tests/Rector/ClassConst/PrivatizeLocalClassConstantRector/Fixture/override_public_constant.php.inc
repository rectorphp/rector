<?php

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class OverridingPublicConstantClass extends DeclaringPublicConstantClass
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

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class OverridingPublicConstantClass extends DeclaringPublicConstantClass
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

<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class OverridingProtectedConstantClass extends DeclaringProtectedConstantClass
{
    const PROTECTED_CONSTANT = false;
}

class ProtectedConstantUser extends DeclaringProtectedConstantClass
{
    public function run()
    {
        return self::PROTECTED_CONSTANT;
    }
}

class DeclaringProtectedConstantClass
{
    const PROTECTED_CONSTANT = true;
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class OverridingProtectedConstantClass extends DeclaringProtectedConstantClass
{
    protected const PROTECTED_CONSTANT = false;
}

class ProtectedConstantUser extends DeclaringProtectedConstantClass
{
    public function run()
    {
        return self::PROTECTED_CONSTANT;
    }
}

class DeclaringProtectedConstantClass
{
    protected const PROTECTED_CONSTANT = true;
}

?>

<?php

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

interface InterfaceWithConstant
{
    const LOCAL_ONLY = true;
}

class ClassExtendingInterface implements InterfaceWithConstant
{
    public function run()
    {
        return self::LOCAL_ONLY;
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

interface InterfaceWithConstant
{
    protected const LOCAL_ONLY = true;
}

class ClassExtendingInterface implements InterfaceWithConstant
{
    public function run()
    {
        return self::LOCAL_ONLY;
    }
}

?>

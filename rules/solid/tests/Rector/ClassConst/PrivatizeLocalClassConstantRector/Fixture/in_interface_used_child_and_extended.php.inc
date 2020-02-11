<?php

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

interface YetAnotherInterfaceWithConstant
{
    const LOCAL_ONLY = true;
}

class YetAnotherClassExtendingInterface implements YetAnotherInterfaceWithConstant
{
    public function run()
    {
        return self::LOCAL_ONLY;
    }
}

class YetForeigner
{
    public function run()
    {
        return YetAnotherClassExtendingInterface::LOCAL_ONLY;
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

interface YetAnotherInterfaceWithConstant
{
    public const LOCAL_ONLY = true;
}

class YetAnotherClassExtendingInterface implements YetAnotherInterfaceWithConstant
{
    public function run()
    {
        return self::LOCAL_ONLY;
    }
}

class YetForeigner
{
    public function run()
    {
        return YetAnotherClassExtendingInterface::LOCAL_ONLY;
    }
}

?>

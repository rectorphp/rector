<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

interface AnotherInterfaceWithConstant
{
    const LOCAL_ONLY = true;
}

class AnotherClassExtendingInterface implements AnotherInterfaceWithConstant
{
    public function run()
    {
        return self::LOCAL_ONLY;
    }
}

class Foreigner
{
    public function run()
    {
        return AnotherInterfaceWithConstant::LOCAL_ONLY;
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

interface AnotherInterfaceWithConstant
{
    public const LOCAL_ONLY = true;
}

class AnotherClassExtendingInterface implements AnotherInterfaceWithConstant
{
    public function run()
    {
        return self::LOCAL_ONLY;
    }
}

class Foreigner
{
    public function run()
    {
        return AnotherInterfaceWithConstant::LOCAL_ONLY;
    }
}

?>

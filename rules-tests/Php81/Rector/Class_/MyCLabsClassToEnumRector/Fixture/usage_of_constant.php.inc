<?php

namespace Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Fixture;

use Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Source\SomeEnum;

final class UsageOfConstant
{
    public function run($value)
    {
        return $value === SomeEnum::VALUE();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Fixture;

use Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Source\SomeEnum;

final class UsageOfConstant
{
    public function run($value)
    {
        return $value === \Rector\Tests\Php81\Rector\Class_\MyCLabsClassToEnumRector\Source\SomeEnum::VALUE;
    }
}

?>

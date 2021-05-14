<?php

namespace Rector\Tests\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector\Fixture;

use Rector\Tests\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector\Source\SomeEnum;

final class UsageOfConstant
{
    public function run($value)
    {
        $compare = SomeEnum::VALUE()->getKey();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector\Fixture;

use Rector\Tests\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector\Source\SomeEnum;

final class UsageOfConstant
{
    public function run($value)
    {
        $compare = \Rector\Tests\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector\Source\SomeEnum::VALUE;
    }
}

?>

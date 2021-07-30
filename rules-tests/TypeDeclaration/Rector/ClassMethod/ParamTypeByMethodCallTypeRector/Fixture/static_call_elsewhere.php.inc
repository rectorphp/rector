<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Source\SomeTypedService;

final class StaticCallElsewhere
{
    public function go($value, $first)
    {
        SomeTypedService::fun($first, $value);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Source\SomeTypedService;

final class StaticCallElsewhere
{
    public function go(string $value, $first)
    {
        SomeTypedService::fun($first, $value);
    }
}

?>

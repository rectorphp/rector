<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

use function Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Source\function_typed;

final class FuncCallElsewhere
{
    public function go($anotherValue, $someValue)
    {
        function_typed($someValue, $anotherValue);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

use function Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Source\function_typed;

final class FuncCallElsewhere
{
    public function go(int|bool $anotherValue, $someValue)
    {
        function_typed($someValue, $anotherValue);
    }
}

?>

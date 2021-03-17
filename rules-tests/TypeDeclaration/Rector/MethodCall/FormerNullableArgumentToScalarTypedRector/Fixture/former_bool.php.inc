<?php

namespace Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowBool;

final class FormerBool
{
    public function run(NowBool $nowBool)
    {
        $nowBool->setValue(null);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowBool;

final class FormerBool
{
    public function run(NowBool $nowBool)
    {
        $nowBool->setValue(false);
    }
}

?>

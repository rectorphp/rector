<?php

namespace Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowBool;

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

namespace Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowBool;

final class FormerBool
{
    public function run(NowBool $nowBool)
    {
        $nowBool->setValue(false);
    }
}

?>

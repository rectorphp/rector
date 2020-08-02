<?php

namespace Rector\Generic\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\ParentMethodFormerInteger;

final class WithParentMethod
{
    public function run(ParentMethodFormerInteger $parentMethodFormerInteger)
    {
        $parentMethodFormerInteger->setValue(null);
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\ParentMethodFormerInteger;

final class WithParentMethod
{
    public function run(ParentMethodFormerInteger $parentMethodFormerInteger)
    {
        $parentMethodFormerInteger->setValue(0);
    }
}

?>

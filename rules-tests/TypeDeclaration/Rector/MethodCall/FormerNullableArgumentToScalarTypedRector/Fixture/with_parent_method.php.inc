<?php

namespace Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\ParentMethodFormerInteger;

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

namespace Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\ParentMethodFormerInteger;

final class WithParentMethod
{
    public function run(ParentMethodFormerInteger $parentMethodFormerInteger)
    {
        $parentMethodFormerInteger->setValue(0);
    }
}

?>

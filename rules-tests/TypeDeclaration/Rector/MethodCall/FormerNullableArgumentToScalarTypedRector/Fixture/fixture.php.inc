<?php

namespace Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowString;

final class SomeClass
{
    public function run(NowString $nowString)
    {
        $nowString->setValue(null);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowString;

final class SomeClass
{
    public function run(NowString $nowString)
    {
        $nowString->setValue('');
    }
}

?>

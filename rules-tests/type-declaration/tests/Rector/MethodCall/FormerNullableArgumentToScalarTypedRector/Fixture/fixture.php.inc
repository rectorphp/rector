<?php

namespace Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowString;

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

namespace Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\NowString;

final class SomeClass
{
    public function run(NowString $nowString)
    {
        $nowString->setValue('');
    }
}

?>

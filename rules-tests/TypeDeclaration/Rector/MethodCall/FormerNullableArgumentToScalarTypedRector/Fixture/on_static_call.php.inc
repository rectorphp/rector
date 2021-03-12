<?php

namespace Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\StaticNowInteger;

final class OnStaticCall
{
    public function run()
    {
        StaticNowInteger::setValue(null);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\StaticNowInteger;

final class OnStaticCall
{
    public function run()
    {
        StaticNowInteger::setValue(0);
    }
}

?>

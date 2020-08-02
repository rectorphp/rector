<?php

namespace Rector\Generic\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\StaticNowInteger;

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

namespace Rector\Generic\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector\Source\StaticNowInteger;

final class OnStaticCall
{
    public function run()
    {
        StaticNowInteger::setValue(0);
    }
}

?>

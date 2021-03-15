<?php

namespace Rector\Tests\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;

final class SomeClass
{
    public function __construct(TypeClassToReplaceMethodCallBy $typeClassToReplaceMethodCallBy)
    {
        $typeClassToReplaceMethodCallBy->someMethod();
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;

final class SomeClass
{
    public function __construct(TypeClassToReplaceMethodCallBy $typeClassToReplaceMethodCallBy)
    {
        $this->someProperty->someMethod();
    }
}

?>

<?php

namespace Rector\Transform\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Fixture;

use Rector\Transform\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;

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

namespace Rector\Transform\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Fixture;

use Rector\Transform\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;

final class SomeClass
{
    public function __construct(TypeClassToReplaceMethodCallBy $typeClassToReplaceMethodCallBy)
    {
        $this->someProperty->someMethod();
    }
}

?>

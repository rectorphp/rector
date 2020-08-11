<?php

namespace Rector\Generic\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;

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

namespace Rector\Generic\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\ReplaceParentCallByPropertyCallRector\Source\TypeClassToReplaceMethodCallBy;

final class SomeClass
{
    public function __construct(TypeClassToReplaceMethodCallBy $typeClassToReplaceMethodCallBy)
    {
        $this->someProperty->someMethod();
    }
}

?>

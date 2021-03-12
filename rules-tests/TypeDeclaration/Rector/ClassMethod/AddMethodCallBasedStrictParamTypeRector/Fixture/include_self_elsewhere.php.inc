<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class IncludeSelfElsewhere
{
    public function __construct(AnotherClassWithUsingSelf $anotherClassWithUsingSelf)
    {
        $anotherClassWithUsingSelf->setIncludeSelfElsewhere($this);
    }
}

final class AnotherClassWithUsingSelf
{
    public function setIncludeSelfElsewhere($some)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class IncludeSelfElsewhere
{
    public function __construct(AnotherClassWithUsingSelf $anotherClassWithUsingSelf)
    {
        $anotherClassWithUsingSelf->setIncludeSelfElsewhere($this);
    }
}

final class AnotherClassWithUsingSelf
{
    public function setIncludeSelfElsewhere(\Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture\IncludeSelfElsewhere $some)
    {
    }
}

?>

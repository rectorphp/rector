<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Source\AnotherClass;

class WithArguments
{
    public function some()
    {
        $this->anotherMethod(new AnotherClass([1, 2, 3]))
            ->someFunction(4)
            ->anotherFunction(5);
    }

    public function anotherMethod(AnotherClass $anotherClass): AnotherClass
    {
        return $anotherClass;
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Source\AnotherClass;

class WithArguments
{
    public function some()
    {
        $anotherClass = new AnotherClass([1, 2, 3]);
        $anotherClass->someFunction(4);
        $anotherClass->anotherFunction(5);
        $this->anotherMethod($anotherClass);
    }

    public function anotherMethod(AnotherClass $anotherClass): AnotherClass
    {
        return $anotherClass;
    }
}

?>

<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Source\AnotherClass;

class MultiChain
{
    public function some()
    {
        $this->anotherMethod(new AnotherClass())
            ->someFunction()
            ->anotherFunction();
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

class MultiChain
{
    public function some()
    {
        $anotherClass = new AnotherClass();
        $anotherClass->someFunction();
        $anotherClass->anotherFunction();
        $this->anotherMethod($anotherClass);
    }

    public function anotherMethod(AnotherClass $anotherClass): AnotherClass
    {
        return $anotherClass;
    }
}

?>

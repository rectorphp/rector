<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\MethodCallOnSetterMethodCallToStandaloneAssignRector\Source\AnotherClass;

class SomeClass
{
    public function some()
    {
        $this->anotherMethod(new AnotherClass())
            ->someFunction();
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

class SomeClass
{
    public function some()
    {
        $anotherClass = new AnotherClass();
        $anotherClass->someFunction();
        $this->anotherMethod($anotherClass);
    }

    public function anotherMethod(AnotherClass $anotherClass): AnotherClass
    {
        return $anotherClass;
    }
}

?>

<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\SetterOnSetterMethodCallToStandaloneAssignRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\SetterOnSetterMethodCallToStandaloneAssignRector\Source\AnotherClass;

class MultiChain
{
    public function some()
    {
        $this->anotherMethod(new AnotherClass())
            ->someFunction()
            ->anoterFuction();
    }

    public function anotherMethod(AnotherClass $anotherClass): AnotherClass
    {
        return $anotherClass;
    }
}

?>
-----
<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\SetterOnSetterMethodCallToStandaloneAssignRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\SetterOnSetterMethodCallToStandaloneAssignRector\Source\AnotherClass;

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

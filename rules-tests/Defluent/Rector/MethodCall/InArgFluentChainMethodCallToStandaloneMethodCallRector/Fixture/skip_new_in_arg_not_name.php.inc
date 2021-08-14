<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\InArgFluentChainMethodCallToStandaloneMethodCallRector\Source\ValueObject;

class SkipNewInArgNotName
{
    public function someFunction(ValueObject $valueObject)
    {
        $this->processFluentClass(
            (new $valueObject->class)->someFunction()->otherFunction()
        );
    }

    public function someFunction2()
    {
        $this->processFluentClass(
            (new $this->getFluentClass())->someFunction()->otherFunction()
        );
    }

    private function getFluentClass()
    {
        return ValueObject::A_CLASS;
    }

    public function processFluentClass($arg)
    {
    }
}

?>

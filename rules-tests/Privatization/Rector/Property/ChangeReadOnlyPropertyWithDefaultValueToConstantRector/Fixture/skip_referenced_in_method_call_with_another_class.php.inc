<?php

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class ReferencedInMethodCall
{
    public function process(array &$value)
    {
    }
}

class SkipReferencedInMethodCallWithAnotherClass
{
    private $value = [];

    public function run()
    {
        $referencedInMethodCall = new ReferencedInMethodCall();
        $referencedInMethodCall->process($this->value);
    }
}

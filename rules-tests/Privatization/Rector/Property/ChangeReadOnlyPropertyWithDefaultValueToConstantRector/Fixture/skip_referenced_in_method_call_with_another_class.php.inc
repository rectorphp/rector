<?php

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

use Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Source\ReferencedInMethodCall;

class SkipReferencedInMethodCallWithAnotherClass
{
    private $value = [];

    public function run()
    {
        $referencedInMethodCall = new ReferencedInMethodCall();
        $referencedInMethodCall->process($this->value);
    }
}

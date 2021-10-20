<?php

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

use Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Source\ReferencedInStaticCall;

final class SkipReferencedInStaticCallWithAnotherClass
{
    private $value = [];

    public function run()
    {
        ReferencedInStaticCall::process($this->value);
    }
}

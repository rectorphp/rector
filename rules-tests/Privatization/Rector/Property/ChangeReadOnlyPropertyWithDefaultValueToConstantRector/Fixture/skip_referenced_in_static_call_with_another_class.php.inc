<?php

namespace Rector\Privatization\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class ReferencedInStaticCall
{
    public static function process(array &$value)
    {
    }
}

final class SkipReferencedInStaticCallWithAnotherClass
{
    private $value = [];

    public function run()
    {
        ReferencedInStaticCall::process($this->value);
    }
}

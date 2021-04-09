<?php

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class AnotherClass
{
    public function process(array &$value)
    {
    }
}

class SkipReferenced2
{
    private $value = [];

    public function run()
    {
        $anotherClass = new AnotherClass();
        $anotherClass->process($this->value);
    }
}

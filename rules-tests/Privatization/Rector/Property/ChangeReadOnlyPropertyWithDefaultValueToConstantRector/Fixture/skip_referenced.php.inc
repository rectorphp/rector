<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

class SkipReferenced
{
    private $value = [];

    public function run()
    {
        $this->process($this->value);
    }

    private function process(array &$value)
    {
    }
}

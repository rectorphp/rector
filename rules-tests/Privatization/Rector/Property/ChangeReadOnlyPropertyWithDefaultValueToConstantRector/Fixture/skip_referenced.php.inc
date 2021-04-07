<?php

namespace Rector\Tests\Privatization\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

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

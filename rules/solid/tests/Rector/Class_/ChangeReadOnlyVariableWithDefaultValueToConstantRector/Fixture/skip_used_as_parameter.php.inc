<?php

namespace Rector\SOLID\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipUsedAsParameter
{
    public function run($items)
    {
        foreach ($items as $key => $value) {
            if ($value === null) {
                $value = [];
            }

            $this->runSomething($value);
        }
    }

    private function runSomething(?array $value)
    {
    }
}

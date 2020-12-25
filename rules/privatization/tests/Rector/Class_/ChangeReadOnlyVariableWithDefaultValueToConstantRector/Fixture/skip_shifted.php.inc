<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipShifted
{
    public function run(string $name, $candidateNames)
    {
        $bareName = $name;
        $prefixes = ['main', 'default'];

        while (in_array($name, $candidateNames, true)) {
            $name = array_shift($prefixes) . ucfirst($bareName);
        }

        return $name;
    }
}

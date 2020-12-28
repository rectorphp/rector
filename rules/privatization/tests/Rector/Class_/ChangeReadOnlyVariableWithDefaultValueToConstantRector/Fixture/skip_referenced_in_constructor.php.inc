<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Source\ReferenceInConstructor;

class SkipReferencedInConstructor
{
    public function run()
    {
        $value = 'name';
        $servicesConfigurator = new ReferenceInConstructor($value);
    }
}

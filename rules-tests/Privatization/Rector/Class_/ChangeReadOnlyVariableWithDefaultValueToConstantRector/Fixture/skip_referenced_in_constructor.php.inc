<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Source\ReferenceInConstructor;

class SkipReferencedInConstructor
{
    public function run()
    {
        $value = 'name';
        $servicesConfigurator = new ReferenceInConstructor($value);
    }
}

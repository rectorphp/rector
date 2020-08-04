<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\SingleSomeClass;
use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\SomeVendorInterface;

class KeepImplementsVendor implements SomeVendorInterface
{
    public function something(SingleSomeClass $class): void
    {
        get_class($class);
    }
}

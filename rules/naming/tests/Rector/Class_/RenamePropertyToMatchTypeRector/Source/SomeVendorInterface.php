<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source;

interface SomeVendorInterface
{
    public function something(SingleSomeClass $class): void;
}

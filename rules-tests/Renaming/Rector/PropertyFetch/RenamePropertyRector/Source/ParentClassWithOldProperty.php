<?php

declare(strict_types=1);

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Source;

class ParentClassWithOldProperty
{
    public bool $oldProperty = false;

    public function run(): bool
    {
        return $this->oldProperty;
    }
}

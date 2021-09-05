<?php

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture;

class SkipNotConfiguredClass
{
    public $oldProperty;

    public function run()
    {
        return $this->oldProperty;
    }
}

?>
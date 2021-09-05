<?php

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture;

class DoNotChangeToPropertyExists
{
    public $oldProperty;
    public $newProperty;

    public function onlyChangePropertyFetch()
    {
        return $this->oldProperty;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture;

class DoNotChangeToPropertyExists
{
    public $oldProperty;
    public $newProperty;

    public function onlyChangePropertyFetch()
    {
        return $this->newProperty;
    }
}

?>

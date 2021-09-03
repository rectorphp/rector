<?php

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture;

class ClassWithOldProperty
{
    public $oldProperty;

    public function run()
    {
        return $this->oldProperty;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture;

class ClassWithOldProperty
{
    public $newProperty;

    public function run()
    {
        return $this->newProperty;
    }
}

?>

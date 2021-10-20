<?php

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture;

use Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Source\ParentClassWithOldProperty;

class ChildClassWithOldProperty extends ParentClassWithOldProperty
{
    public bool $oldProperty = true;

    public function run(): bool
    {
        return $this->oldProperty;
    }
}

class PropertyFetchFromChildClass
{
    public ChildClassWithOldProperty $classWithOldProperty;

    public function run()
    {
        return $this->classWithOldProperty->oldProperty;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture;

use Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Source\ParentClassWithOldProperty;

class ChildClassWithOldProperty extends ParentClassWithOldProperty
{
    public bool $newProperty = true;

    public function run(): bool
    {
        return $this->newProperty;
    }
}

class PropertyFetchFromChildClass
{
    public ChildClassWithOldProperty $classWithOldProperty;

    public function run()
    {
        return $this->classWithOldProperty->newProperty;
    }
}

?>

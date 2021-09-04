<?php

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture;

class ClassWithOldProperty2
{
    public $oldProperty;
}

class OtherClass
{
    public ClassWithOldProperty2 $classWithOldProperty;

    public function run()
    {
        return $this->classWithOldProperty->oldProperty;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\Fixture;

class ClassWithOldProperty2
{
    public $newProperty;
}

class OtherClass
{
    public ClassWithOldProperty2 $classWithOldProperty;

    public function run()
    {
        return $this->classWithOldProperty->newProperty;
    }
}

?>

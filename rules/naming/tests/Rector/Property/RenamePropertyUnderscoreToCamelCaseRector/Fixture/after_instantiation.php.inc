<?php

namespace Rector\Naming\Tests\Rector\Property\RenamePropertyUnderscoreToCamelCaseRector\Fixture;

class SomeClass2
{
    public $some_property;

    public function run(): void
    {
        $obj = new self();
        $obj->some_property;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Property\RenamePropertyUnderscoreToCamelCaseRector\Fixture;

class SomeClass2
{
    public $someProperty;

    public function run(): void
    {
        $obj = new self();
        $obj->someProperty;
    }
}

?>

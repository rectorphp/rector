<?php

namespace Rector\Naming\Tests\Rector\Property\RenamePropertyUnderscoreToCamelCaseRector\Fixture;

class SomeClass
{
    private $some_property;

    public function run(): void
    {
        $this->some_property;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Property\RenamePropertyUnderscoreToCamelCaseRector\Fixture;

class SomeClass
{
    private $someProperty;

    public function run(): void
    {
        $this->someProperty;
    }
}

?>

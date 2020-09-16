<?php

namespace Rector\Naming\Tests\Rector\Property\RenamePropertyUnderscoreToCamelCaseRector\Fixture;

class SomeClass3
{
    private $some_property = 1;

    public function run(): void
    {
        $this->some_property;
    }
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Property\RenamePropertyUnderscoreToCamelCaseRector\Fixture;

class SomeClass3
{
    private $someProperty = 1;

    public function run(): void
    {
        $this->someProperty;
    }
}

?>

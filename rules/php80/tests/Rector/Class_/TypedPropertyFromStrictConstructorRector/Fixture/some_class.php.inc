<?php

namespace Rector\Php80\Tests\Rector\Class_\TypedPropertyFromStrictConstructorRector\Fixture;

class SomeObject
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\TypedPropertyFromStrictConstructorRector\Fixture;

class SomeObject
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

?>

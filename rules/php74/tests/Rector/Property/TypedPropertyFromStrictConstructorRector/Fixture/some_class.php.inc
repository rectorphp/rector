<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

class SomeClass
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

class SomeClass
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

?>

<?php

namespace Rector\Tests\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector\Fixture;

class SomeClass
{
    public readonly string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector\Fixture;

class SomeClass
{
    /**
     * @readonly
     */
    public string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}

?>

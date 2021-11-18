<?php

namespace Rector\Tests\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector\Fixture;

class SomeClass
{
    /**
     * Property comment.
     * @readonly
     */
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
     * Property comment.
     * @readonly
     */
    public string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}

?>

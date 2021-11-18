<?php

namespace Rector\Tests\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector\Fixture;

class SomeClass
{
    public function __construct(
        public readonly string $foo = 'foo'
    )
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector\Fixture;

class SomeClass
{
    public function __construct(
        public string $foo = 'foo'
    )
    {
    }
}

?>

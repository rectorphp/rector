<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

class AncestorClass
{
    public function test(array $input)
    {
        /* ... */
    }
}

class ChildClass extends AncestorClass
{
    public function test($input) // type omitted for $input
    {
        /* ... */
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

class AncestorClass
{
    /**
     * @param mixed[] $input
     */
    public function test($input)
    {
        /* ... */
    }
}

class ChildClass extends AncestorClass
{
    public function test($input) // type omitted for $input
    {
        /* ... */
    }
}

?>

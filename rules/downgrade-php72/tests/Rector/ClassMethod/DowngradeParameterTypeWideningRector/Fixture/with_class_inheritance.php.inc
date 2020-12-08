<?php

namespace Rector\DowngradePhp72\Tests\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

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

class AffectedChildClass extends AncestorClass
{
    public function test(array $input)
    {
        /* ... */
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

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

class AffectedChildClass extends AncestorClass
{
    /**
     * @param mixed[] $input
     */
    public function test($input)
    {
        /* ... */
    }
}

?>

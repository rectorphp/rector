<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface WhateverInterface
{
    public function test(string $input);
}

abstract class AbstractSomeAncestorClass implements WhateverInterface
{
}

class SomeChildClass extends AbstractSomeAncestorClass
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

interface WhateverInterface
{
    /**
     * @param string $input
     */
    public function test($input);
}

abstract class AbstractSomeAncestorClass implements WhateverInterface
{
}

class SomeChildClass extends AbstractSomeAncestorClass
{
    public function test($input) // type omitted for $input
    {
        /* ... */
    }
}

?>

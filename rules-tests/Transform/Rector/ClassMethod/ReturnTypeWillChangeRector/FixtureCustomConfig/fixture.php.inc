<?php

namespace Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\FixtureCustomConfig;

use Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\Source\Options;

class SomeClass extends Options
{
    public function offsetGet($offset)
    {
    }

    public function offsetExists($offset)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\FixtureCustomConfig;

use Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\Source\Options;

class SomeClass extends Options
{
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
    }
}

?>

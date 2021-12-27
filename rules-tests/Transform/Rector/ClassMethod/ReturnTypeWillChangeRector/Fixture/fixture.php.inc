<?php

namespace Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\Fixture;

use Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\Source\Options;

class SomeClass extends Options
{
    public function offsetGet($offset)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\Fixture;

use Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\Source\Options;

class SomeClass extends Options
{
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
    }
}

?>

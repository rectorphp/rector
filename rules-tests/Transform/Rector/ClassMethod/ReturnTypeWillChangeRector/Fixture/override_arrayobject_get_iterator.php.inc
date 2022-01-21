<?php

namespace Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\Fixture;

class OverrideArrayObjectGetIterator extends \ArrayObject
{
    public function getIterator()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\Fixture;

class OverrideArrayObjectGetIterator extends \ArrayObject
{
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
    }
}

?>

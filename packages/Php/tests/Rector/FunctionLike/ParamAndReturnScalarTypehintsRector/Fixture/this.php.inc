<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class ThisClass
{
    /**
     * @return $this
     */
    function someFunction()
    {
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class ThisClass
{
    /**
     * @return $this
     */
    function someFunction(): self
    {
    }
}

?>

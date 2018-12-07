<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class BoolClass
{
    /**
     * @param true $ojoj
     * @param FALSE $ojoj2
     * @param true|false $ojoj3
     * @param null|true|false $ojoj4
     * @param int|null|true|false $ojoj5
     * @param null|BOOL|true|false $ojoj6
     * @return false
     */
    function someFunction($ojoj, $ojoj2, $ojoj3, $ojoj4, $ojoj5, $ojoj6)
    {
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class BoolClass
{
    /**
     * @param true $ojoj
     * @param FALSE $ojoj2
     * @param true|false $ojoj3
     * @param null|true|false $ojoj4
     * @param int|null|true|false $ojoj5
     * @param null|BOOL|true|false $ojoj6
     * @return false
     */
    function someFunction(bool $ojoj, bool $ojoj2, bool $ojoj3, ?bool $ojoj4, $ojoj5, ?bool $ojoj6): bool
    {
    }
}

?>

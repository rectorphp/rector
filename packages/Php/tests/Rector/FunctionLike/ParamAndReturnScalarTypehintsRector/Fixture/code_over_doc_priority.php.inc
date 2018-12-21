<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class CodeOverDocPriority
{
    /**
     * @return bool
     */
    public function getNumber()
    {
        return 5;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class CodeOverDocPriority
{
    /**
     * @return bool
     */
    public function getNumber(): int
    {
        return 5;
    }
}

?>

<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class ThisClass
{
    /**
     * @param $this $param
     * @param null|$this $param2
     * @return $this
     */
    function someFunction($param, $param2)
    {
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class ThisClass
{
    /**
     * @param $this $param
     * @param null|$this $param2
     * @return $this
     */
    function someFunction($param, $param2): self
    {
    }
}

?>

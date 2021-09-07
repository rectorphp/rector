<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class ParentParamObjectChildParamClassName
{
    /**
     * @param object $obj
     */
    protected function someFunction($obj)
    {
    }
}

class ChildParamClassName extends ParentParamObjectChildParamClassName
{
    /**
     * @param \stdClass $obj
     */
    protected function someFunction($obj)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class ParentParamObjectChildParamClassName
{
    protected function someFunction(object $obj)
    {
    }
}

class ChildParamClassName extends ParentParamObjectChildParamClassName
{
    /**
     * @param \stdClass $obj
     */
    protected function someFunction($obj)
    {
    }
}

?>
